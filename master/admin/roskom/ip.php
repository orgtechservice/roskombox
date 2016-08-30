<?php
/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Информация о решениях суда по блокировке нежелательного контента');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/ip');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");
$engine->access->requireAccess("roskom:edit_access");

$file_path = $engine->config->read('site_prefix') ."admin/roskom/";

$form = $engine->post->newForm('admin/roskom/ip');

$form->values['order_id']=intval(trim($_GET['id']));
$order_id=$form->values['order_id'];
$r=$engine->roskom->select('roskom_order_ip','*',"ip_order_id=$order_id");
$ips=array();$i=0;

while($f = $engine->roskom->fetchAssoc($r))
{
	$ips[$i]=$f['ip_text'];
	$i++;
}

$engine->roskom->freeResult($r);

$engine->tpl->set_tag('ips',$ips);
$engine->tpl->set_tag('order_id', $order_id);

if (isset($_POST['add_ip']))
	$flag_add = 1;

if (isset($_POST['save']) )
{
	$form = $engine->post->parse('admin/roskom/ip');
	
	if (! preg_match('/^([0-9]{1,3}\.){2,2}[0-9]{1,3}/',$form->values['ip_text']))
	{
		$form->error("orders:wrong_ip_address");
	}

	if ( $form->hasErrors() )
	{
		$order_id=$form->values['order_id'];
		$engine->tpl->set_tag('form', $form->render());
		echo $engine->tpl->render('std/page');
		exit;
	}
	
	$ip_text=$engine->roskom->quote($form->values['ip_text']);
	$engine->roskom->insert("roskom_order_ip",array('ip_order_id'=>$form->values['order_id'],
							'ip_text'=>$ip_text,
	));
	$order_id=$form->values['order_id'];
	header("Location:" .$file_path ."ip.php?id=$order_id");
}

if (isset($_POST['cancel']))
{
	$form = $engine->post->parse('admin/roskom/domain');
	$order_id=$form->values['order_id'];
	header("Location:" .$file_path ."ip.php?id=$order_id");
}		

$engine->tpl->set_tag('flag_add', $flag_add);
$engine->tpl->set_tag('form', $form->render());

echo $engine->tpl->render('std/page');
