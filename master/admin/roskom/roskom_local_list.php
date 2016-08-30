<?php

/**
* Скрипт предназначен для отдачи редуктору локальных решений по блокировкам
*/

require dirname(__FILE__) . "../../system.php";

header("Content-Type: text/plain;charset=utf-8");

// Сначала URL-ки
$statement = $engine->roskom->select("roskom_order_url", "url_text", "");
while($row = $engine->roskom->fetchAssoc($statement)) {
	$url = $row['url_text'];
	if(!preg_match("/^https:/", $url)) {
		$replaced = preg_replace_callback('/%\w{2}/', "replace_handler", $url);
		echo $replaced . "\n";
	}
}
$engine->roskom->freeResult($statement);

// Потом домены
$statement = $engine->roskom->select("roskom_order_domain", "*", '');
while($row = $engine->roskom->fetchAssoc($statement)) {
	echo 'http://', $row['domain_text'], "\n";
}
$engine->roskom->freeResult($statement);


function replace_handler($matches) {
	$result = "";
	foreach($matches as $match) {
		$deco = urldecode($match);
		if (preg_match('"[/=&]"', $deco))
			$result .= $match;
		else $result .= $deco;
	}
	return $result;
}
