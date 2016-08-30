<?php

require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Проверялка');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/checker_avail');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

$checker_id = (int) @ $_REQUEST['id'];
$checker = $engine->roskom->checkerById($checker_id);
if(!$checker) $engine->common->notFound();

$links = $engine->roskom->queryAll("SELECT * FROM roskom_available_links WHERE link_checker_id = $checker_id");
$engine->tpl->set_tag('links', $links);

echo $engine->roskom->renderCheckerTabs($checker, "admin/roskom/checker_avail.php?id=$checker_id");
