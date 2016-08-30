<?php

/**
* Модуль для обработки реестра роскомнадзора
*
* (c) Zolotov Alex, 2012
*     zolotov-alex@shamangrad.net
*     http://shamangrad.net/
*/
class mod_roskom extends mod_mysqli
{
	/**
	* Конструктор модуля
	*
	* @param LightEngine менеджер модулей
	* @retval LightModule модуль
	*/
	public static function create(LightEngine $engine) {
		$path = makepath(DIR_ROOT, 'config', 'db_roskom.php');
		$roskom_config = require $path;
		$roskom = new self($engine);
		$roskom->connect($roskom_config);
		return $roskom;
	}
	
	/**
	*Перекодирование в punycode
	*@param string текст для перекодирования
	*@retval string текст в punycode
	*/
	public function encode($text)
	{
		$punycode = new idna_convert();
		$tmp = mb_convert_encoding ($text,'UTF-8');
		$encode=$punycode->encode($tmp);
		return $encode;
	}
	
	/**
	*Перекодирование punycode в понятный текст
	*@param string текст в punycode
	*@retval string перекодированный текст
	*/
	public function decode($text)
	{
		$punycode = new idna_convert();
		$tmp=mb_convert_encoding ($text,'UTF-8');
		$decode=$punycode->decode($tmp);
		return $decode;
	}

	/**
	* URL-ки должны быть только в UTF-8 в соответствии с http://docs.carbonsoft.ru/pages/viewpage.action?pageId=42827787
	*/
	private function preprocessUrl($url) {
		//mb_detect_order("UTF-8,CP1251,KOI8-R,ISO-8859-1");
		$encoding = mb_detect_encoding($url, mb_detect_order(), true);
		return ($encoding == 'UTF-8') ? $url : iconv($encoding, "UTF-8", $url);
	}

	private function domainName($url) {
		$re = '#(https?)://(([^/]*)\.([^/:\.]*))(:(\d+))?#i';
		$match = [];
		$result = preg_match($re, $url, $match);
		if($result) {
			$domain = $match[2];
			if($domain[strlen($domain) - 1] === '.')
				$domain = substr($domain, 0, strlen($domain) - 1);
			return $domain;
		} else {
			return '';
		}
	}
	
	/**
	*Парсинг реестра и запись данных в базу
	*@param array текст запроса
	*/
	public function parseRegister($text) {
		$register = $this->roskomparser->parseRegister($text);
		if($register) {
			$now = time();

			$this->begin();
			$this->delete('roskom_content', '');
			$this->delete('roskom_url', '');
			$this->delete('roskom_domain', '');
			$this->delete('roskom_ip', '');
			$this->delete('roskom_subnet', '');

			foreach($register['content'] as $content) {
				$content_id = intval($content['meta']['id']);
				$block_type = @ $content['meta']['blockType'];
				if(empty($block_type)) $block_type = 'default';

				$this->insertQuoted('roskom_content', [
					'content_id' => $content_id,
					'content_includeTime' => @ $content['meta']['includeTime'],
					'decision_date' => @ $content['decision']['date'],
					'decision_number' => @ $content['decision']['number'],
					'decision_org' => @ $content['decision']['org'],
					'content_block_type' => $block_type,
				]);
			
				foreach($content['url'] as $url) {
					$url_utf8 = $this->preprocessUrl($url);

					$this->insert('roskom_url', [
						'url_content_id' => $content_id,
						'url_text' => $this->quote($url_utf8),
					]);

					$hash = $this->quote(md5($url_utf8));
					$check = $this->countRows('roskom_url_stat', "us_hash = $hash");
					if($check) {
						$this->update('roskom_url_stat', ['us_utime' => $now], "us_hash = $hash");
					} else {
						$this->insert('roskom_url_stat', [
							'us_url' => $this->quote($url),
							'us_hash' => $hash,
							'us_ctime' => $now,
							'us_utime' => $now,
							'us_content_id' => $content_id,
							'us_domain' => $this->quote($this->domainName($url_utf8)),
						]);
					}
				}
			
				foreach($content['domain'] as $domain) {
					/*if(preg_match('/^www\./', $domain)) {
						$domain = substr_replace($domain, "", 0, 4);
					}*/

					if($domain[strlen($domain) - 1] === '.')
						$domain = substr($domain, 0, strlen($domain) - 1);

					$this->insert('roskom_domain', [
						'domain_content_id' => $content_id,
						'domain_text' => $this->quote($domain),
						'domain_sys' => $this->quote($this->roskom->encode($domain)),
						'domain_view' => $this->quote($this->roskom->decode($domain))
					]);

					$url = "http://$domain/";
					$hash = $this->quote(md5($url));

					$check = $this->countRows('roskom_url_stat', "us_hash = $hash");
					if($check) {
						$this->update('roskom_url_stat', ['us_utime' => $now], "us_hash = $hash");
					} else {
						$this->insert('roskom_url_stat', [
							'us_url' => $this->quote($url),
							'us_hash' => $hash,
							'us_ctime' => $now,
							'us_utime' => $now,
							'us_content_id' => $content_id,
							'us_domain' => $this->quote($this->domainName($url)),
							'us_from_domain' => $this->quote('yes'),
						]);
					}
				}

				foreach($content['ipSubnet'] as $subnet) {
					$this->insertQuoted('roskom_subnet', ['subnet_content_id' => $content_id, 'subnet_text' => $subnet]);
					$this->eventmon->fireEvent('roskom', 'SubnetDetected#$content_id', "Обнаружена блокировка подсети в записи $content_id");
				}
			
				foreach($content['ip'] as $ip) {
					$this->insert('roskom_ip', [
						'ip_content_id' => $content_id,
						'ip_text' => $this->quote($ip)
					]);
				}
			}

			// Теперь добавим искусственно в таблицу URL-ок полностью блокируемые сайты, добавляя им флаг url_from_domain = 'yes'
			$this->exec("INSERT INTO roskom_url (url_content_id, url_text, url_from_domain) SELECT content_id, CONCAT('http://', domain_text, '/'), 'yes' FROM roskom_content AS c LEFT JOIN roskom_domain AS d ON c.content_id = d.domain_content_id WHERE c.content_block_type = 'domain'");

			$this->commit();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	*Получение массива ip-адресов для хоста
	*@param string доменное имя хоста
	*@retval array ip-адресов, в случае неудачи возвращает false
	*/
	public function getIP($text)
	{
		if($ips=gethostbynamel($text))
		{
			$res=array();
			$i=0;
			foreach($ips as $ip)
			{
				$res[$i]['rip_domain_id']=intval($i+1);
				$res[$i]['rip_address']=$this->quote($ip);
				$i++;
			}
			return $res;
		}
		return false;
	}
	
	/**
	*Получение массива ip-адресов для хоста
	*@param string доменное имя хоста
	*@retval array ip-адресов, в случае неудачи возвращает false
	*/
	public function getIPex($text)
	{
		if($ips=gethostbynamel($text))
		{
			$res=array();
			$i=0;
			foreach($ips as $ip)
			{
				$res[$i]['except_domain_id']=intval($i+1);
				$res[$i]['except_address']=$this->quote($ip);
				$i++;
			}
			return $res;
		}
		return false;
	}

	public function log($message, $trace = false, $type = 'info') {
		if(!in_array($type, ['info', 'error', 'warning'])) $type = 'info';
		$this->exec("INSERT INTO roskom_log (log_time, log_message, log_type, log_script_name) VALUES (" . time() . ", " . $this->quote($message) . ", '{$type}', " . $this->quote(basename(@ $_SERVER['SCRIPT_NAME'])) . ")");
		if($trace) echo "[$type] $message\n";
	}

	public function logManualOperation($message, $type = 'info', $operator_id) {
		$operator_id = intval($operator_id);
		if(!in_array($type, ['info', 'error', 'warning'])) $type = 'info';
		//$this->exec("INSERT INTO roskom_log (log_time, log_message, log_type, log_operator_id) VALUES (" . time() . ", " . $this->quote($message) . ", '{$type}', $operator_id)");
		$this->syslog->put('roskom', "[$type] $message", time(), $operator_id);
	}

	public function storeZipFile($raw_data) {
		$time = time();
		$this->exec("INSERT INTO roskom_registry (registry_load_time, registry_zip_file) VALUES (" . $time . ", " . $this->quote($raw_data) . ")");
		return $time;
	}


	public static function cidrMatch($ip, $cidr) {
		list($subnet, $mask) = explode('/', $cidr);
		return (((ip2long($ip) & ~((1 << (32 - $mask)) - 1) ) == ip2long($subnet)));
	}

	public static function cidrMatchAny($ip, $networks) {
		foreach($networks as $network) {
			if(self::cidrMatch($ip, $network)) return true;
		}
		return false;
	}

	public static function isLocal($ip) {
		return self::cidrMatchAny($ip, ['127.0.0.0/8', '192.168.0.0/16', '10.0.0.0/8', '172.16.0.0/12']);
		// return preg_match("^1(27|92.168|0|72.(1[6-9]|2[0-9]3[01]))[0-9.]*$", $ip); // Вариант, предложенный Олегом из CarbonSoft
		// return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
	}

	public static function getAddressStatus($ip) {
		return self::isLocal($ip) ? 'Локальный' : 'Внешний';
	}

	public static function resolveDomain($domain) {
		$ips = gethostbynamel($domain);
		foreach($ips as $ip) {
			yield ['address' => $ip, 'status' => self::getAddressStatus($ip)];
		}
	}

	public function checkDomainBlockStatus($domain) {
		$result = [];
		$statement = $this->query("SELECT decision_number, decision_date, decision_org, YEAR(content_includeTime) AS it_year, MONTH(content_includeTime) AS it_month, DAY(content_includeTime) AS it_day, domain_text FROM (roskom_domain AS d JOIN roskom_content AS c ON d.domain_content_id = c.content_id) WHERE c.content_block_type IN ('domain', 'domain-mask') AND d.domain_text LIKE " . $this->quote("%$domain"));
		while($row = $this->fetchAssoc($statement)) {
			$result[] = "Домен {$row['domain_text']} <b>заблокирован</b> по решению суда {$row['decision_number']} от {$row['decision_date']}<br />Внесён в реестр по распоряжению: <b>{$row['decision_org']}</b>, дата: {$row['it_day']}.{$row['it_month']}.{$row['it_year']}";
		}
		$this->freeResult($statement);
		if(!count($result)) $result[] = 'Домен не присутствует в списке <b>полностью</b> блокируемых со стороны РКН';
		return $result;
	}

	public function checkURLBlockStatus($domain) {
		$result = [];
		$statement = $this->query("SELECT url_text, decision_number, decision_date, decision_org, YEAR(content_includeTime) AS it_year, MONTH(content_includeTime) AS it_month, DAY(content_includeTime) AS it_day FROM ((roskom_content AS c JOIN roskom_url AS u ON u.url_content_id = c.content_id) JOIN roskom_domain AS d ON d.domain_content_id = c.content_id) WHERE domain_text = " . $this->quote($domain));
		while($row = $this->fetchAssoc($statement)) {
			$url = htmlspecialchars($row['url_text']);
			$result[] = "<b>Заблокирована одна из страниц сайта</b> по решению суда {$row['decision_number']} от {$row['decision_date']}<br />Запись внесена в реестр по распоряжению: <b>{$row['decision_org']}</b>, дата: {$row['it_day']}.{$row['it_month']}.{$row['it_year']}<br/>URL: <a href=\"$url\">$url</a>";
		}
		if(!count($result)) $result[] = 'Сайт не содержит страниц, блокируемых РКН';
		$this->freeResult($statement);
		return $result;
	}

	public function checkLocalDomainBlockStatus($domain) {
		$result = [];
		$statement = $this->query("SELECT * FROM roskom_orders AS o LEFT JOIN roskom_order_domain AS d ON o.order_id = d.domain_order_id WHERE d.domain_text = " . $this->quote($domain));
		while($row = $this->fetchAssoc($statement)) {
			$result[] = "Домен <b>заблокирован</b>: {$row['order_info']}";
		}
		$this->freeResult($statement);
		if(!count($result)) $result[] = 'Домен не присутствует в списке <b>полностью</b> блокируемых по решениям местных органов';
		return $result;
	}

	public function checkLocalURLBlockStatus($domain) {
		$result = [];
		$statement = $this->query("SELECT * FROM roskom_orders AS o RIGHT JOIN roskom_order_url AS u ON o.order_id = u.url_order_id WHERE url_text LIKE '%" . $this->escape($domain) . "%'");
		while($row = $this->fetchAssoc($statement)) {
			$url = htmlspecialchars($row['url_text']);
			$result[] = "<b>Заблокирована одна из страниц сайта</b>: <a href=\"$url\">$url</a>";
		}
		$this->freeResult($statement);
		if(!count($result)) $result[] = 'Домен не присутствует в списке <b>полностью</b> блокируемых по решениям местных органов';
		return $result;
	}

	public function checkManualBlockStatus($domain) {
		return [
			(bool)($this->countRows('roskom_domain_manual', 'manual_domain_name = ' . $this->quote($domain))),
			(bool)($this->countRows('roskom_domain_except', 'except_domain_name = ' . $this->quote($domain))),
		];
	}

	public function validateCheckerForm($form) {
		$ip_address = @ $form->values['ip'];
		$max_available = @ $form->values['max_available'];
		if(!preg_match('#^([0-9]+)%?$#', $max_available)) $form->error('roskom:wrong_max_available');
		if(!filter_var($ip_address, FILTER_VALIDATE_IP)) $form->error('roskom:wrong_checker_ip');
		if($this->countRows('roskom_checkers', "checker_ip = " . $this->quote($ip_address))) $form->error('roskom:checker_ip_already_exists');
	}

	public function formatCheckerRow(& $row) {
		if(!$row['checker_last_scan_time'])
			$row['checker_last_scan_time_formatted'] = 'не осуществлялась';
		else
			$row['checker_last_scan_time_formatted'] = $this->common->formatDate($row['checker_last_scan_time']);
	}

	public function formatScanRow(& $row) {
		$row['scan_when_formatted'] = $this->common->formatDate($row['scan_when']);
		$real_available_percent = (float)($row['scan_urls_available']) / (float)($row['scan_urls_total']) * 100;
		$row['scan_percent_available'] = sprintf("%.2f", $real_available_percent);
		$minutes = floor($row['scan_time'] / 60);
		$seconds = $row['scan_time'] % 60;
		$row['scan_time_formatted'] = "{$minutes}m {$seconds}s";
	}

	public function checkerById($checker_id) {
		$checker_id = (int) $checker_id;
		$statement = $this->query("SELECT c.*, s.scan_urls_total, s.scan_urls_available FROM roskom_checkers AS c LEFT JOIN roskom_checker_scans AS s ON c.checker_last_scan_id = s.scan_id WHERE checker_id = $checker_id");
		$row = $this->fetchAssoc($statement);
		$this->freeResult($statement);
		if($row) $this->formatCheckerRow($row);
		return $row;
	}

	public function renderCheckerTabs($checker, $current) {
		$checker_id = (int) $checker['checker_id'];

		$this->tpl->set_tag("TABSTITLE", "<a href='checkers.php'>Проверялки</a>&nbsp;»&nbsp;{$checker['checker_shortname']}");
		$this->tabs->addTab("admin/roskom/checker.php?id=$checker_id", "Информация");
		$this->tabs->addTab("admin/roskom/checker_scans.php?id=$checker_id", "Проверки");
		$this->tabs->addTab("admin/roskom/checker_avail.php?id=$checker_id", "Доступные ссылки");
		$this->tabs->addTab("admin/roskom/checker_edit.php?id=$checker_id", "Редактировать");
		
		return $this->tabs->render('std/page', $current);
	}

	public function checkUnblockedPages($domain) {
		$result = [];
		$deadline = time() - 86400;
		$statement = $this->query("SELECT * FROM roskom_url_stat AS s LEFT JOIN roskom_checkers AS c ON c.checker_id = s.us_checker_id WHERE us_ptime > $deadline AND us_domain LIKE '%" . $this->escape($domain) . "%'");
		while($row = $this->fetchAssoc($statement)) {
			$url = htmlspecialchars($row['us_url']);
			$when = $this->common->formatDate($row['us_ptime']);
			$created = $this->common->formatDate($row['us_ctime']);
			$updated = $this->common->formatDate($row['us_utime']);
			$domain = ($row['us_from_domain'] == 'yes') ? '(на основе домена) ' : '';
			$result[] = "<b>Одна из страниц</b>: <a href=\"$url\">$url</a>, внесённая в БД $domain$created и обновлённая $updated, была доступна на проверялке {$row['checker_shortname']} $when";
		}
		$this->freeResult($statement);
		if(!count($result)) $result[] = 'Проблем с блокировками, связанных с этим ресурсом, не зафиксировано';
		return $result;
	}
}
