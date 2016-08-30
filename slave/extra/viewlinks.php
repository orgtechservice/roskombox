<?php

define('SCAN_FILENAME', '/srv/roskom/available-links.txt');

$links = file(SCAN_FILENAME);
$links = array_map(function(& $line) { return trim($line); }, $links);
$links = array_filter($links, function(& $val) { return !empty($val); });

function htmlPage(& $content) {
	header("Content-Type: text/html;charset=utf-8");
	return "<html><head><title>Ссылки, открывшиеся при последней проверке</title></head><body><h1>Ссылки, открывшиеся при последней проверке</h1><div>$content</div></body></html>";
}

function renderLinks(& $links) {
	return "<ul>" . implode (
		'',
		array_map (
			function($link) {
				$link = htmlspecialchars($link);
				return "<li><a href=\"$link\">$link</a></li>";
			},
			$links
		)
	) . "</ul>";
}

$content = renderLinks($links);
echo htmlPage($content);
