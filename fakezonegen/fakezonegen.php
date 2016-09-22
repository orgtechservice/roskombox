#!/usr/bin/php
<?php

$config_filename = '/etc/maycloud/fakezonegen.php';
if(!file_exists($config_filename) || !is_readable($config_filename)) {
	echo "Cannot read $config_filename\n";
	die(-1);
}
require $config_filename;

require '/var/lib/maycloud/php-modules/idna_convert.php';

define('RE_DOMAIN', '#(https?)://(([^/]*)\.([^/:\.]*))(:(\d+))?#i');

function getLastLine($file) {
	$line = '';

	$f = fopen($file, 'r');
	$cursor = -1;

	fseek($f, $cursor, SEEK_END);
	$char = fgetc($f);

	while(in_array($char, ["\r", "\n", ' '], true)) {
		fseek($f, $cursor --, SEEK_END);
		$char = fgetc($f);
	}

	while($char !== false && $char !== "\n" && $char !== "\r") {
		$line = $char . $line;
		fseek($f, $cursor --, SEEK_END);
    	$char = fgetc($f);
    }

    fclose($f);
	return $line;
}

function validateDomain($link, & $match) {
	return preg_match(RE_DOMAIN, $link, $match);
}

function usingWrongPort($match) {
	return array_key_exists(6, $match) && !in_array($match[6], ['80', '8080']);
}

function isIpAddress($domain) {
	return filter_var($domain, FILTER_VALIDATE_IP);
}

function isCyrillic($tld) {
	return !preg_match('#^[a-z]+$#', $tld);
}

function renderZone($domain) {
	global $config;
	return "zone \"$domain\" {\n    type master;\n    file \"{$config['TRAP']}\";\n    allow-query { \"any\"; };\n};";
}

function fireEvent($event_id, $message) {
	global $config, $lanbill;

	$time = time();
	$lanbill->beginTransaction();

	try {
		$message = $lanbill->quote($message);
		$event_id = $lanbill->quote($event_id);
		$module_name = $lanbill->quote('roskom');

		// Вдруг событие уже существует
		$statement = $lanbill->prepare("SELECT * FROM eventmon_events WHERE event_module = $module_name AND event_id = $event_id FOR UPDATE");
		$statement->execute();
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		$statement->closeCursor();

		// Сохраним или обновим событие
		if($row) {
			$lanbill->exec("UPDATE eventmon_events SET event_updated = $time, event_message = $message WHERE event_id = $event_id");
		} else {
			$data = ['event_id' => $event_id, 'event_module' => $module_name, 'event_when' => $time, 'event_updated' => $time, 'event_message' => $message];
			$fields = implode(', ', array_keys($data));
			$values = implode(', ', array_values($data));
			$lanbill->exec("INSERT INTO eventmon_events ($fields) VALUES ($values)");
		}

		$lanbill->commit();
		return true;
	} catch(Exception $e) {
		$lanbill->rollback();
		return false;
	}
}

function getLastRegistryUpdate() {
	global $lanbill;
	$statement = $lanbill->prepare("SELECT config_value FROM config WHERE config_name = 'roskom:lastDumpDate'");
	$statement->execute();
	$row = $statement->fetch(PDO::FETCH_ASSOC);
	$statement->closeCursor();
	return $row ? $row['config_value'] : false;
}

function getOurLastDate() {
	global $config;
	$m = [];
	$line = getLastLine($config['FILENAME']);
	if(!preg_match('/^#DUMP COMPLETE <(\d+)>$/', $line, $m)) {
		return false;
	} else {
		return @ (int) $m[1];
	}
}

/* Далее непосредственно логика скрипта */

// Сначала подключимся к БД админки
$lanbill = NULL;
try {
	$lanbill = new PDO (
		"mysql:dbname={$config['LANBILL_DB_NAME']};host={$config['LANBILL_DB_HOST']}",
		$config['LANBILL_DB_USER'],
		$config['LANBILL_DB_PASSWD'],
		[PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"]
	);
} catch(Exception $e) {
	// Если не удалось, то максимум, что мы можем — поругаться в стандартный вывод
	echo "MySQL connection failed\n{$e->getMessage()}\n";
	die(-1);
}

// Получим дату последнего обновления реестра
$last_dump_date = getLastRegistryUpdate();

// Проверим, существует ли файл зон
if(file_exists($config['FILENAME'])) {
	// Запросим дату обновления файла зон
	$our_last_date = getOurLastDate();

	// Если даты совпадают, нам ничего не нужно делать
	if($last_dump_date === $our_last_date) {
		die(0);
	}
}

// Теперь установим соединение с основной БД Роскома
$db = NULL;
try {
	$db = new PDO (
		"mysql:dbname={$config['DB_NAME']};host={$config['DB_HOST']}",
		$config['DB_USER'],
		$config['DB_PASSWD'],
		[PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"]
	);
} catch(Exception $e) {
	// В случае неудачи можно ещё попробовать создать аварийное событие, вдруг получится
	$message = "MySQL connection failed\n{$e->getMessage()}\n";
	echo $message;
	fireEvent('fzg_roskom', $message);
	die(-1);
}

$IDN = new idna_convert();

$ips = [];
$domains = [];

try {
	// Сначала запросим HTTPS-ресурсы из реестра
	$statement = $db->prepare("SELECT url_text FROM roskom_url WHERE LOWER(url_text) LIKE 'https://%'");
	$statement->execute();
	while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
		$match = [];
		if(validateDomain($row['url_text'], $match)) {
			if(isIpAddress($match[2])) {
				$ips[$match[2]] = $match[2];
			} else {
				//$domains[$match[2]] = isCyrillic($match[4]) ? $IDN->encode($match[2]) : $match[2];
				$domains[$match[2]] = $IDN->encode($match[2]);
			}
		}
	}
	$statement->closeCursor();

	// Теперь запросим ресурсы с левым портом из реестра
	$statement = $db->prepare("SELECT url_text FROM roskom_url WHERE LOWER(url_text) REGEXP '^http://[^/]+:[0-9]+'");
	$statement->execute();
	while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
		$match = [];
		if(validateDomain($row['url_text'], $match)) {
			if(usingWrongPort($match)) {
				if(isIpAddress($match[2])) {
					$ips[$match[2]] = $match[2];
				} else {
					//$domains[$match[2]] = isCyrillic($match[4]) ? $IDN->encode($match[2]) : $match[2];
					$domains[$match[2]] = $IDN->encode($match[2]);
				}
			}
		}
	}
	$statement->closeCursor();

	// Теперь запросим HTTPS-ресурсы локальных решений
	$statement = $db->prepare("SELECT url_text FROM roskom_order_url WHERE LOWER(url_text) LIKE 'https://%'");
	$statement->execute();
	while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
		$match = [];
		if(validateDomain($row['url_text'], $match)) {
			if(isIpAddress($match[2])) {
				$ips[$match[2]] = $match[2];
			} else {
				//$domains[$match[2]] = isCyrillic($match[4]) ? $IDN->encode($match[2]) : $match[2];
				$domains[$match[2]] = $IDN->encode($match[2]);
			}
		}
	}
	$statement->closeCursor();

	// Теперь запросим ресурсы с левым портом из локальных решений
	$statement = $db->prepare("SELECT url_text FROM roskom_order_url WHERE lower(url_text) REGEXP '^http://[^/]+:[0-9]+'");
	$statement->execute();
	while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
		$match = [];
		if(validateDomain($row['url_text'], $match)) {
			if(usingWrongPort($match)) {
				if(isIpAddress($match[2])) {
					$ips[$match[2]] = $match[2];
				} else {
					//$domains[$match[2]] = isCyrillic($match[4]) ? $IDN->encode($match[2]) : $match[2];
					$domains[$match[2]] = $IDN->encode($match[2]);
				}
			}
		}
	}
	$statement->closeCursor();
} catch(Exception $e) {
	$report = fireEvent('fzg_roskom_fetch', "Fake zone generator failed to fetch data from roskom database");
	if(!$report) {
		echo "MySQL error\n{$e->getMessage()}\n";
	}
	die(-1);
}

// Теперь от БД можно отключиться
unset($db);

$zones = [];
foreach($domains as & $value) {
	if($value[strlen($value) - 1] === '.') $value = substr($value, 0, strlen($value) - 1);
	$zones[$value] = renderZone($value);
}

$data = implode("\n", $zones) . "\n\n#DUMP COMPLETE <$last_dump_date>\n";
file_put_contents($config['FILENAME'], $data);

// Снова проверим, присутствует ли метка времени
// Если строки с датой в файле не оказалось, необходимо громко заматериться вслух
$our_last_date = getOurLastDate();
if($our_last_date === false) {
	fireEvent('fzg_broken_file', "Fake zone file is broken: no last dump date present");
} else {
	exec($config['RELOAD_CMD']);
}

// Отключаемся от второй базы
unset($lanbill);
