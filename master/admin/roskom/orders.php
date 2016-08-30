<?php

/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../system.php";
$engine->access->requireAccess("roskom:view_access");

$engine->tpl->set_tag('TITLE', 'Информация о решениях суда по блокировке нежелательного контента');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/orders');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();

$r = $engine->roskom->select('roskom_orders', '*', '', 'order_id ASC');
$orders = array ();
while ( $f = $engine->roskom->fetchAssoc($r) )
{
	$order_id = intval($f['order_id']);
	$orders[$order_id] = $f;
}
$engine->roskom->freeResult($r);

$r = $engine->roskom->select('roskom_order_url', '*', '');
while ( $f = $engine->roskom->fetchAssoc($r) )
{
	$order_id = intval($f['url_order_id']);
	$orders[$order_id]['url'][] = $f['url_text'];
}
$engine->roskom->freeResult($r);

$r = $engine->roskom->select('roskom_order_domain', '*', '');
while ( $f = $engine->roskom->fetchAssoc($r) )
{
	$order_id = intval($f['domain_order_id']);
	$orders[$order_id]['domain'][] = $f['domain_view'];
}
$engine->roskom->freeResult($r);

$r = $engine->roskom->select('roskom_order_ip', '*', '');
while ( $f = $engine->roskom->fetchAssoc($r) )
{
	$order_id = intval($f['ip_order_id']);
	$orders[$order_id]['ip'][] = $f['ip_text'];
}
	$engine->roskom->freeResult($r);
	$engine->tpl->set_tag("orders", $orders);
	
	echo $engine->tpl->render('std/page');
