<?php
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Поиск по реестру роскомнадзора');
$engine->tpl->set_tag('MODULE', basename(__DIR__));
$engine->tpl->set_tag('CONTENT', 'admin/roskom/search');

$engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

// Начало

$domain = is_array($domain = @ $_REQUEST['domain']) ? '' : $domain;

if($domain) {
	//$engine->tpl->set_tag('ips', mod_roskom::resolveDomain($domain));
	$engine->tpl->set_tag('domain', htmlspecialchars($domain));

	$engine->tpl->set_tag('blocked', $engine->roskom->checkDomainBlockStatus($domain));
	$engine->tpl->set_tag('urls', $engine->roskom->checkURLBlockStatus($domain));

	$engine->tpl->set_tag('local_blocked', $engine->roskom->checkLocalDomainBlockStatus($domain));
	$engine->tpl->set_tag('local_urls', $engine->roskom->checkLocalURLBlockStatus($domain));

	$engine->tpl->set_tag('unblocked', $engine->roskom->checkUnblockedPages($domain));

	list($blocked, $excepted) = $engine->roskom->checkManualBlockStatus($domain);
	$engine->tpl->set_tag('manually_blocked', $blocked);
	$engine->tpl->set_tag('manually_excepted', $excepted);
}

echo $engine->tpl->render('std/page');
