<?php

/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Информация о решениях суда по блокировке нежелательного контента');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/url');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");
$engine->access->requireAccess("roskom:edit_access");

$file_path = $engine->config->read('site_prefix') ."admin/roskom/";

$form = $engine->post->newForm('admin/roskom/url');

$form->values['order_id']=intval(trim($_GET['id']));
$order_id=$form->values['order_id'];
$r=$engine->roskom->select('roskom_order_url','*',"url_order_id=$order_id");
$urls=array();$i=0;

while($f = $engine->roskom->fetchAssoc($r))
{
	$urls[$i]=$f['url_text'];
}

$engine->roskom->freeResult($r);

$engine->tpl->set_tag('urls',$urls);
$engine->tpl->set_tag('order_id', $order_id);

if (isset($_POST['add_url']))
	$flag_add = 1;

if (isset($_POST['save']) )
{
	$form = $engine->post->parse('admin/roskom/url');

	if ( $form->hasErrors() )
	{
		$engine->tpl->set_tag('form', $form->render());
		echo $engine->tpl->render('std/page');
		exit;
	}
	
	$url_text=$engine->roskom->quote($form->values['url_text']);
	$engine->roskom->insert("roskom_order_url",array('url_order_id'=>$form->values['order_id'],
							'url_text'=>$url_text,
	));
	$order_id=$form->values['order_id'];
	header("Location:" .$file_path ."url.php?id=$order_id");
}

if (isset($_POST['cancel']))
{
	$form = $engine->post->parse('admin/roskom/domain');
	$order_id=$form->values['order_id'];
	header("Location:" .$file_path ."url.php?id=$order_id");
}		

$engine->tpl->set_tag('flag_add', $flag_add);
$engine->tpl->set_tag('form', $form->render());

echo $engine->tpl->render('std/page');
