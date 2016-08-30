<?php

require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Проверялка');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/checker');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

$checker_id = (int) @ $_REQUEST['id'];
$checker = $engine->roskom->checkerById($checker_id);
if(!$checker) $engine->common->notFound();

$engine->tpl->set_tag('TITLE', "Проверялка: {$checker['checker_shortname']}");
$engine->tpl->set_tag('checker', $checker);

echo $engine->roskom->renderCheckerTabs($checker, "admin/roskom/checker.php?id=$checker_id");
