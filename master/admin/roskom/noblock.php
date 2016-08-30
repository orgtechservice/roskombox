<?php

require dirname(__FILE__) . "/../system.php";

switch(@ $_GET['type']) {
	case 'beeline': $xcode = 2; break;
	case 'local': $xcode = 5; break;
	case 'empty': $xcode = 7; break;
	default: $xcode = 1; break;
}

$titles = [
	1 => 'Не блокируются',
	2 => 'Блокируются Билайном',
	5 => 'Резолвятся в локальные IP',
	7 => 'Открылась пустая страница',
];

$engine->tpl->set_tag('TITLE', $titles[$xcode]);
$engine->tpl->set_tag('CONTENT', 'admin/roskom/noblock');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

$urls = [];
$statement = $engine->roskom->query('SELECT url_text, url_ip FROM roskom_check_results WHERE url_xcode = ' . $xcode);
while($row = $engine->roskom->fetchAssoc($statement)) {
	$urls[] = ['address' => $row['url_text'], 'ip' => $row['url_ip']];
}
$engine->roskom->freeResult($statement);

$engine->tpl->set_tag('urls', $urls);

echo $engine->tpl->render('std/page');
