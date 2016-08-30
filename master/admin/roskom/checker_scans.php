<?php

require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Результаты проверок');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/checker_scans');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

$checker_id = (int) @ $_REQUEST['id'];
$checker = $engine->roskom->checkerById($checker_id);
if(!$checker) $engine->common->notFound();

$scans = $engine->roskom->queryAll("SELECT * FROM roskom_checker_scans WHERE scan_checker_id = $checker_id ORDER BY scan_when DESC LIMIT 50 OFFSET 0");
foreach($scans as & $scan) $engine->roskom->formatScanRow($scan);
$engine->tpl->set_tag('scans', $scans);

echo $engine->roskom->renderCheckerTabs($checker, "admin/roskom/checker_scans.php?id=$checker_id");
