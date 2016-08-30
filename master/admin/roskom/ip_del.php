<?php
/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Удаление ip-адреса из списка');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/ip_del');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

$file_path = $engine->config->read('site_prefix') ."admin/roskom/";
$order_id = intval(trim($_GET['id']));
$ip_text = trim($_GET['name']);
$form = $engine->post->newForm('admin/roskom/ip_del');
$form->values['order_id']=$order_id;
$form->values['ip_text']=$ip_text;

/**
*Удаляем запись
*/
if (isset($_POST['delete']))
{
	$form = $engine->post->parse('admin/roskom/ip_del');
	$order_id = $form->values['order_id'];
	$ip_text = $form->values['ip_text'];
	$where='ip_order_id='.$order_id.' AND ip_text='.$engine->roskom->quote($ip_text);
	$engine->roskom->delete('roskom_order_ip',$where);
	header("Location:" .$file_path ."ip.php?id=$order_id");
}

if (isset($_POST['cancel']))
{
	$form = $engine->post->parse('admin/roskom/ip_del');
	$order_id = $form->values['order_id'];
	$ip_text = $form->values['ip_text'];
	header("Location:" .$file_path ."ip.php?id=$order_id");
}

$engine->tpl->set_tag('file_path', $file_path);
$engine->tpl->set_tag('order_id', $order_id);
$engine->tpl->set_tag('ip_text',$ip_text);
$engine->tpl->set_tag('form', $form->render());
echo $engine->tpl->render('std/page');
