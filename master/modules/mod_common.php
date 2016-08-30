<?php

/**
* Разнообразные функции, которые могут пригодиться в самых разных местах
* © WST, 2015
*/

final class mod_common extends LightModule
{
	const DATETIME_REGEX = '#^([0-9]{2})\.([0-9]{2})\.([0-9]{4}) ([0-9]{2}):([0-9]{2})$#';

	/**
	* Конструктор модуля
	* @param LightEngine менеджер модулей
	* @retval LightModule модуль
	*/
	public static function create(LightEngine $engine) {
		return new self($engine);
	}

	/**
	* Сформировать и выдать страницу 404 ошибки, затем прервать выполнение скрипта
	*/
	public function notFound($layout = 'std') {
		$this->tpl->set_tag('CONTENT', "std/404");
		echo $this->tpl->render("$layout/page");
		die();
	}

	/**
	* Сформировать и выдать страницу успешного выполнения операции
	*/
	public function done($redirect, $send_header = false) {
		if($send_header) {
			header("Location: $redirect");
		}
		$this->tpl->set_tag('CONTENT', 'std/ok');
		$this->tpl->set_tag('REDIRECT', $redirect);
		echo $this->tpl->render('std/page');
		die();
	}

	public function showAgainIfHasErrors($form, $layout = 'std', $preprocessor = NULL) {
		if(!$form->hasErrors()) return;
		if(!is_null($preprocessor) && is_callable($preprocessor)) $preprocessor($form);
		$this->tpl->set_tag('form', $form->render());
		$this->tpl->set_tag('CONTENT', "$layout/form");
		echo $this->tpl->render("$layout/page");
		die();
	}

	/**
	* Сформировать и выдать страницу с произвольным сообщением, и прервать выполнение скрипта
	* По желанию можно передать конкретную ссылку для редиректа, иначе будет ссылаться на $_SERVER[HTTP_REFERER]
	* @param string или array, сообщение для показа. Если передан array, то каждый элемент будет
	* считаться новой строкой.
	* @param string URL для редиректа
	*/
	public function showStopMessage($message, $redirect='') {
		if (empty($redirect)) {
			$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
		}
		if (is_array($message)) $message = implode("\n",$message);
		$this->tpl->set_tag('CONTENT', 'std/message');
		$this->tpl->set_tag('message', $message);
		$this->tpl->set_tag('REDIRECT', $redirect);
		echo $this->tpl->render('std/page');
		exit();
	}

	/**
	* Получить всё содержимое таблицы с именем $table
	*/
	public function listTable($table, $where = '', $order = '', $preprocessor = NULL, $what = '*', $limit = NULL, $offset = NULL) {
		$statement = $this->db->select($table, $what, $where, $order, $limit, $offset);
		if(is_callable($preprocessor)) {
			while($row = $this->db->fetchAssoc($statement)) {
				$preprocessor($row);
				yield $row;
			}
		} else {
			while($row = $this->db->fetchAssoc($statement)) yield $row;
		}
		$this->db->freeResult($statement);
	}

	/**
	* Преобразовать строку даты/времени в метку времени Unix
	*/
	public function dateToUnix($date, $end = false) {
		$match = [];
		list($h, $m, $s) = $end ? [23, 59, 59] : [0, 0, 0];
		if(preg_match('#^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$#', $date, $match)) {
			return mktime($h, $m, $s, $match[2], $match[3], $match[1]);
		}
		if(preg_match('#^([0-9]{2})\.([0-9]{2})\.([0-9]{4})$#', $date, $match)) {
			return mktime($h, $m, $s, $match[2], $match[1], $match[3]);
		}
		return mktime($h, $m, $s);
	}

	public function datetimeToUnix($datetime) {
		$match = [];
		if(preg_match(self::DATETIME_REGEX, $datetime, $m)) {
			// hour, minute, second, month, day, year
			return mktime($m[4], $m[5], 0, $m[2], $m[1], $m[3]);
		}
		return false;
	}

	/**
	* Преобразовать UNIX timestamp в дату либо для HTML5 date field, либо для вывода
	*/
	public function formatDate($unixtime, $for_html5 = false) {
		$format = $for_html5 ? 'Y-m-d' : 'd.m.y H:i:s';
		return date($format, $unixtime);
	}
	
	/**
	* Преобразовать UNIX timestamp в дату либо для HTML5 date field, либо для вывода
	*/
	public function formatDateWithoutSec($unixtime, $for_html5 = false) {
		$format = $for_html5 ? 'Y-m-d' : 'd.m.y H:i';
		return date($format, $unixtime);
	}

	/**
	* Функция решает следующую проблему: получает на вход дату в формате гггг-мм-дд или дд.мм.гггг,
	* выдавая на выходе значение первого вида, пригодное для SQL-запросов.
	* Потребность в этом связана с тем, что не все браузеры поддерживают HTML5 date
	*/
	public function dateToSQL($date) {
		$match = [];

		if(preg_match('#^([0-9]{2})\.([0-9]{2})\.([0-9]{4})$#', $date, $match)) {
			return $this->db->quote("{$match[3]}\-{$match[2]}\-{$match[1]}");
		}

		return $this->db->quote($date);
	}

	public function dateFromSQL($date) {
		$date = explode('-', $date);
		return "{$date[2]}.{$date[1]}.{$date[0]}";
	}

	public function validateRequestEnum($parameter, array $allowed_values) {
		return (!is_string($parameter) || !in_array($parameter, $allowed_values)) ? $allowed_values[0] : $parameter;
	}

	public function formatBytes($bytes, $precision = 2) {
		$units = ['б', 'кб', 'Мб', 'Гб', 'Тб'];
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= (1 << (10 * $pow));
		return round($bytes, $precision) . " {$units[$pow]}";
	}

	public function prepareUserPhone($unformatted_phone) {
		$phone = preg_replace('#[^0-9]#', '', $unformatted_phone);
		if(strlen($phone) > 11) $phone = substr($phone, 0, 11);
		return $phone;
	}

	/**
	* Создать разбивку на страницы
	*/
	public function createPager($element_count, $elements_per_page, $current_page, $link_template, $additional_class = 'pagination-centered', $previous_page = 'Previous page', $next_page = 'Next page') {
		$pages = [];
		$make_link = function($template, $page_number) { return str_replace('{page}', $page_number, $template); };
		$pages_to = ($element_count % $elements_per_page) ? floor($element_count / $elements_per_page) + 1 : ($element_count / $elements_per_page);
		
		$pages[0] = ($current_page == 1) ? '' : '<li><a href="' . $make_link($link_template, $current_page - 1) . '" title="' . $previous_page . '">◀</a></li>';
		
		if($pages_to < 10) {
			for($i = 1; $i <= $pages_to; $i ++) {
				$selected = ($i == $current_page) ? ' class="active"' : '';
				$pages[$i] = '<li' . $selected . '><a href="' . $make_link($link_template, $i) . '">' . $i . '</a></li>';
			}
		} else {
			for($i = 1; $i <= 5; $i ++) {
				$selected = ($i == $current_page) ? ' class="active"' : '';
				$pages[$i] = '<li' . $selected . '><a href="' . $make_link($link_template, $i) . '">' . $i . '</a></li>';
			}
			
			if($current_page > 5 && $current_page < $pages_to - 4) {
				if($current_page > 6) {
					$pages[6] = '…';
				}
				$pages[$current_page] = '<li class="active"><a href="' . $make_link($link_template, $current_page) . '">' . $current_page . '</a></li>';
				if($current_page < $pages_to - 5) {
					$pages[$pages_to - 5] = '…';
				}
			} else {
				$pages[6] = '…';
			}
			
			for($i = $pages_to - 4; $i <= $pages_to; $i ++) {
				$selected = ($i == $current_page) ? ' class="active"' : '';
				$pages[$i] = '<li' . $selected . '><a href="' . $make_link($link_template, $i) . '">' . $i . '</a></li>';
			}
		}
		
		$pages[$pages_to + 1] = ($current_page == $pages_to) ? '' : '<li><a href="' . $make_link($link_template, $current_page + 1) . '" title="' . $next_page . '">▶</a></li>';
		
		return '<div class="pagination' . ($additional_class ? " $additional_class" : '') . '"><ul>' . implode('', $pages) . '</ul></div>';
	}

	public function authorizeMonitor() {
		if($this->session->authorized()) return true;
		$monitor_ip = $this->config->read("eventmon:monitor_ip");
		if($_SERVER['REMOTE_ADDR'] == $monitor_ip) {
			$user = $this->db->selectOne("users", "*", "user_login = 'monitor'");
			$this->session->start($user, false);
		} else {
			die('Доступ для вашего IP-адреса запрещён');
		}
	}
	
	/**
	*преобразует строку вида "512K" в число бит
	*@param string строка для преобразования
	*@retval int число бит
	*/
	public function stringToBits($rate)
	{
		$num=intval(substr($rate, 0, strlen($rate)-1)); 
		switch ($rate[strlen($rate)-1]) {
			case 'k':return $num*1000;
			case 'K':return $num*1000;
			case 'm':return $num*1000*1000;
			case 'M':return $num*1000*1000;
			case 'g':return $num*1000*1000*1000;
			case 'G':return $num*1000*1000*1000;
		}
		return intval($rate);
	}
	
	/**
	*преобразует строку вида "100K" в число байт
	*@param string строка для преобразования
	*@retval int число байт
	*/
	public function stringToBytes($rate)
	{
		$num=intval(substr($rate, 0, strlen($rate)-1)); 
		switch ($rate[strlen($rate)-1]) {
			case 'k':return $num*1024;
			case 'K':return $num*1024;
			case 'm':return $num*1024*1024;
			case 'M':return $num*1024*1024;
			case 'g':return $num*1024*1024*1024;
			case 'G':return $num*1024*1024*1024;
		}
		return intval($rate);
	}
		
	/**
	*преобразует число бит в строку вида 512K
	*@param int число для преобразования
	*@retval string строка с суффиксом
	*/
	public function bitsToString($rate)
	{
		$suf='';
		if ($rate%1000==0)
		{
			$suf='K';
			$rate=$rate/1000;
			if ($rate%1000==0)
			{
				$suf='M';
				$rate=$rate/1000;
				if ($rate%1000==0)
				{
					$suf='G';
					$rate=$rate/1000;
				}
			}
		}
		return "$rate$suf";
	}
	
	/**
	*преобразует число байт в строку вида 512K
	*@param int число для преобразования
	*@retval string строка с суффиксом
	*/
	public function bytesToString($rate)
	{
		$suf='';
		if ($rate%1024==0)
		{
			$suf='K';
			$rate=$rate/1024;
			if ($rate%1024==0)
			{
				$suf='M';
				$rate=$rate/1024;
				if ($rate%1024==0)
				{
					$suf='G';
					$rate=$rate/1024;
				}
			}
		}
		return "$rate$suf";
	}
	
	/**
	*Получить для адреса подсеть
	*@param string ip-адрес
	*@retval string подсеть
	*/
	public function get_subnet($ip)
	{
		if (strpos($ip,"/")!==FALSE)
		{
			list($ip_a, $bits)=explode('/', $ip);
			$bits=intval($bits);
			$ip_a=ip2long($ip_a);
			$mask=0xFFFFFFFF<<(32 - $bits);
			$subnet_address = long2ip($ip_a & $mask);
			$subnet = "$subnet_address/$bits";
			return $subnet;
		}
		return $ip;
	}

	public function ucfirst($str) {
		$fc = mb_strtoupper(mb_substr($str, 0, 1));
		return $fc . mb_substr($str, 1);
	}

	/**
	* См также http://php.net/manual/ru/dateinterval.format.php
	*/
	public function formatSeconds($seconds) {
		if($seconds < 60) {
			return "{$seconds}s";
		} elseif($seconds < 3600) {
			$minutes = floor($seconds / 60);
			$seconds %= 60;
			return "{$minutes}m{$seconds}s";
		} elseif ($seconds < 86400) {
			$hours = floor($seconds / 3600);
			$minutes = floor(($seconds - ($hours * 3600)) / 60);
			return "{$hours}h{$minutes}m";
		} else {
			$days = floor($seconds / 86400);
			$hours = floor(($seconds - ($days * 86400)) / 3600);
			$minutes = floor(($seconds - ($days * 86400) - ($hours * 3600)) / 60);
			return "{$days}d{$hours}h{$minutes}m";
		}
	}
	
	/**
	* Получить массив для постраничной разбивки результата
	*@param int число элементов
	*@param int число элементов на странице
	*@param int номер текущей страницы
	*@param string ссылка на страницу для которой делается разбивка
	*@retval mixed массив с номерами страниц
	*/
	public function getPages($num, $n,$p, $link) {
		
		if ($num%10==0) $cnt_page = intval(  $num / 10 ) ;
		else $cnt_page = intval(($num / 10) + 1 ) ;

		if ( ( $p % $n ) !== 0 )
		{
			$a = intval( $p / $n ) * $n + 1;
			$b = intval( $p / $n ) * $n + $n;
		}
		else if ( ( $p % $n ) == 0 )
		{
			$a = ( intval( $p / $n ) - 1 ) * $n + 1;
			$b = ( intval( $p / $n ) - 1 ) * $n + $n;
		}
		$pages=array();
		if ( $p > 1 ) 
		{
			$q = $p - 1;
			$pages[]=array('num'=>$q, 'link'=>"$link$q", 'pos'=>'first', 'active'=>'non');
		}
		else $pages[]=array('num'=>0, 'link'=>"", 'pos'=>'first');

		for ( $i = $a; $i <= $b; $i++ )
		{
			if ( $i <= $cnt_page )
			{	
				if ($i!=$p) $pages[]=array('num'=>$i, 'link'=>"$link$i", 'pos'=>'into', 'active'=>'non');
				else $pages[]=array('num'=>$i, 'link'=>"$link$i", 'pos'=>'into', 'active'=>'yes');
			}
		}

		if ( $p < $cnt_page ) 
		{
			$q = $p + 1;
			$pages[]=array('num'=>$q, 'link'=>"$link$q", 'pos'=>'last', 'active'=>'non');
			
		}
		else $pages[]=array('num'=>0, 'link'=>"", 'pos'=>'last', 'active'=>'non');
		
		return $pages;
		
	}

	public function weekDayName($index) {
		return [
			'Воскресенье',
			'Понедельник',
			'Вторник',
			'Среда',
			'Четверг',
			'Пятница',
			'Суббота'
		][$index];
	}
}
