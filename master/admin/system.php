<?php

/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../core.php";

$engine = new LightEngine();
$engine->tplc->functions['nl2br'] = 'nl2br';
$engine->tplc->functions['abs'] = 'abs';

$engine->tpl->set_tag('LAYOUT', 'layout/cloud');
$engine->tpl->set_tag('LBR', '{');
$engine->tpl->set_tag('RBR', '}');
$engine->tpl->open('INFO');
	$engine->tpl->set_tag('PREFIX', $engine->config->read('site_prefix', '/'));
	$engine->tpl->set_tag('SKINDIR', $engine->config->read('site_prefix', '/') . 'themes/default');
	$engine->tpl->set_tag('IPv6', preg_match('/^(::ffff:)?\d+\.\d+\.\d+\.\d+$/i', $_SERVER['REMOTE_ADDR']) ? false : true);
	$engine->tpl->set_tag('REMOTE_ADDR', $_SERVER['REMOTE_ADDR']);
	$engine->tpl->set_tag('SITEMODE', $engine->config->read('site_mode', 'stand'));
$engine->tpl->close();
$engine->session->init('admin');

if($session = $engine->session->getSessionInfo()) {
	$engine->tpl->set_tag('SESSION', $session);
	$engine->tpl->set_tag('access', [
		"duty_support" => $engine->access->checkAccess('lan:duty_support'),
		"switch" => $engine->access->checkAccess('switch:view_access'),
		"report" => $engine->access->checkAccess('report:view_access'),
		"edit_lan" => $engine->access->checkAccess('lan:edit_lan'),
		"task" => $engine->access->checkAccess('task:close_access'),
	]);
}
