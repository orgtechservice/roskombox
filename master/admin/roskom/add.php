<?php

/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Информация о решениях суда по блокировке нежелательного контента');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/add');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");
$engine->access->requireAccess("roskom:edit_access");

$form = $engine->post->newForm('admin/roskom/add');
$form->options['order_active']=array(array('value'=>'active','title'=>'решение активно'),
								array('value'=>'not_active','title'=>'решение не активно'));

if ( isset($_POST['save']) )
{
	$form = $engine->post->parse('admin/roskom/add');
	if(! preg_match('/^([0-9]{2,2}.){2,2}[0-9]{4,4}$/',$form->values['order_date']))
	{
		$form->error("orders:wrong_date_format");
	}
	if((! preg_match('/^([0-9]{2,2}.){2,2}[0-9]{4,4}$$/',$form->values['order_end_date']))&&(! $form->values['order_end_date']==""))
	{
		$form->error("orders:wrong_date_format");
	}
	if ( $form->hasErrors() )
	{
		$form->options['order_active']=array(array('value'=>'active','title'=>'решение активно'),
								array('value'=>'not_active','title'=>'решение не активно'));
		$engine->tpl->set_tag('form', $form->render());
		echo $engine->tpl->render('std/page');
		exit;
	}
	$order_active=$form->values['order_active']=='active'?1:0;
	$engine->roskom->insert('roskom_orders',array('order_date'=>strtotime($form->values['order_date']),
					'order_active'=>$order_active,
					'order_info'=>$engine->roskom->quote($form->values['order_info'])
					));
	header("Location:" .$file_path ."orders.php");
}
if (isset($_POST['cancel']))
	header("Location:" .$file_path ."orders.php");

$engine->tpl->set_tag('form', $form->render());
echo $engine->tpl->render('std/page');
