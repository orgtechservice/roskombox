<?php

/**
* Скрипт предназначен для отдачи редуктору актульного списка Роскомнадзора
*/

require dirname(__FILE__) . "../../system.php";

Header("Content-Type: text/plain");

$r = $engine->roskom->select("roskom_url", "url_text", "");
while ($f = $engine->roskom->fetchAssoc($r)) {
	$url = $f['url_text'];
	if ( preg_match("/^https:/", $url) == 0 ) {
// 		$decoded = urldecode($url);
		$replaced = preg_replace_callback('/%\w{2}/', "replace_handler", $url);
// 		if ($decoded != $replaced) echo "\n$url\n$decoded\n$replaced\n";
		echo $replaced . "\n";
	}
}

function replace_handler ($matches) {
	$result = "";
	foreach($matches as $match) {
		$deco = urldecode($match);
		if (preg_match('"[/=&]"', $deco))
			$result .= $match;
		else $result .= $deco;
	}
	return $result;
}
