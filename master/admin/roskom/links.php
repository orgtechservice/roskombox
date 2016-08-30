<?php

require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Статистика по неблокируемым за 24 часа');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/links');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

$deadline = time() - 86400;
$rows = $engine->roskom->queryAll("SELECT * FROM roskom_url_stat AS s LEFT JOIN roskom_checkers AS c ON c.checker_id = s.us_checker_id WHERE us_ptime > $deadline ORDER BY us_ptime DESC");
foreach($rows as & $row) {
	$row['us_ptime_formatted'] = $engine->common->formatDate($row['us_ptime']);
	$row['us_ctime_formatted'] = $engine->common->formatDate($row['us_ctime']);
	$row['us_utime_formatted'] = $engine->common->formatDate($row['us_utime']);
}

$engine->tpl->set_tag('links', $rows);

echo $engine->tpl->render('std/page');
