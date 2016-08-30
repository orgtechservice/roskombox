<?php
/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Информация о решениях суда по блокировке нежелательного контента');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/domain');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");
$engine->access->requireAccess("roskom:edit_access");

$file_path = $engine->config->read('site_prefix') ."admin/roskom/";

$form = $engine->post->newForm('admin/roskom/domain');

$form->values['order_id']=intval(trim($_GET['id']));
$order_id=$form->values['order_id'];
$r=$engine->roskom->select('roskom_order_domain','*',"domain_order_id=$order_id");
$domains=array();$i=0;

while($f = $engine->roskom->fetchAssoc($r))
{
	$domains[$i]['domain_sys']=$f['domain_sys'];
	$domains[$i]['domain_text']=$f['domain_text'];
	$domains[$i]['domain_view']=$f['domain_view'];
	$i++;
}

$engine->roskom->freeResult($r);

$engine->tpl->set_tag('domains',$domains);
$engine->tpl->set_tag('order_id', $order_id);

if (isset($_POST['add_domain']))
	$flag_add = 1;

if (isset($_POST['save']) )
{
	$form = $engine->post->parse('admin/roskom/domain');

	if ( $form->hasErrors() )
	{
		$engine->tpl->set_tag('form', $form->render());
		echo $engine->tpl->render('std/page');
		exit;
	}
	
	$domain_text=$engine->roskom->quote($form->values['order_domain']);
	if (preg_match('/^www\./',$domain_text))
	{
		$domain_text=substr_replace($domain_text,"",0,4);
	}
	$domain_sys=$engine->roskom->encode($domain_text);
	$domain_view=$engine->roskom->decode($domain_text);
	$engine->roskom->insert("roskom_order_domain",array('domain_order_id'=>$form->values['order_id'],
							'domain_text'=>$domain_text,
							'domain_sys'=>$domain_sys,
							'domain_view'=>$domain_view));
	$order_id=$form->values['order_id'];
	header("Location:" .$file_path ."domain.php?id=$order_id");
}

if (isset($_POST['cancel']))
{
	$form = $engine->post->parse('admin/roskom/domain');
	$order_id=$form->values['order_id'];
	header("Location:" .$file_path ."domain.php?id=$order_id");
}
		

$engine->tpl->set_tag('flag_add', $flag_add);
$engine->tpl->set_tag('form', $form->render());

echo $engine->tpl->render('std/page');
