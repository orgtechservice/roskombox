<?php

/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/system.php";

try
{
	$engine->tpl->set_tag('TITLE', 'Авторизация');
	$engine->tpl->set_tag('CONTENT', 'std/form');
	
	$engine->session->close();
	
	$link = $engine->config->read('site_prefix');
	header("Location: $link");
	$engine->tpl->set_tag('CONTENT', 'std/ok');
	$engine->tpl->set_tag('REDIRECT', $link);
	echo $engine->tpl->render('std/page');
}
catch (tpl_exception $e)
{
	echo "TPL error: " . $e->getMessage() . "\n";
}

?>