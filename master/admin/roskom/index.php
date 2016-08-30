<?php

/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Реестр Роскомнадзора');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/index');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

echo $engine->tpl->render('std/page');
