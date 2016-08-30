<?php

require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Точки проверок доступности ресурсов');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/checkers');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

$checkers = $engine->roskom->queryAll("SELECT * FROM roskom_checkers AS c LEFT JOIN roskom_checker_scans AS s ON c.checker_last_scan_id = s.scan_id");
foreach($checkers as & $row) $engine->roskom->formatCheckerRow($row);

$engine->tpl->set_tag('checkers', $checkers);

echo $engine->tpl->render('std/page');
