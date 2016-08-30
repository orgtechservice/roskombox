<?php

/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/system.php";

$engine->tpl->set_tag('TITLE', 'Панель управления');
$engine->tpl->set_tag('CONTENT', 'admin/index');


$operator = $engine->session->authorize();

echo $engine->tpl->render('std/page');
