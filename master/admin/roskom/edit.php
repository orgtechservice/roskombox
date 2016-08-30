<?php
/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Информация о решениях суда по блокировке нежелательного контента');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/edit');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");
$engine->access->requireAccess("roskom:edit_access");

$file_path = $engine->config->read('site_prefix') ."admin/roskom/";
$form = $engine->post->newForm('admin/roskom/edit');
$form->options['order_active']=array(array('value'=>'active','title'=>'решение активно'),
								array('value'=>'not_active','title'=>'решение не активно'));

if((! isset($_POST['save']))||(! isset($_POST['cancel'])))
{
	$order_id=intval(trim($_GET['id']));
	$engine->tpl->set_tag('order_id',$order_id);
	$r=$engine->roskom->select('roskom_orders','*',"order_id='".$order_id."'");
	$f = $engine->roskom->fetchAssoc($r);
	$form->values['order_id']=$order_id;
	$form->values['order_date']=date("d.m.Y",$f['order_date']);
	$form->values['order_info']=$f['order_info'];
	$form->values['order_end_date']=$f['order_end_date']==NULL?NULL:date("d.m.Y",$f['order_end_date']);
	$form->values['order_active']=$f['order_active']==1?'active':'not_active';
	
	
}

if (isset($_POST['save']) )
{
	$form = $engine->post->parse('admin/roskom/edit');
	if(! preg_match('/^([0-9]{2,2}.){2,2}[0-9]{4,4}$/',$form->values['order_date']))
	{
		$form->error("orders:wrong_date_format");
	}
	if((! preg_match('/^([0-9]{2,2}.){2,2}[0-9]{4,4}$/',$form->values['order_end_date']))&&(! $form->values['order_end_date']==""))
	{
		$form->error("orders:wrong_date_format");
	}
	
	if ( $form->hasErrors() )
	{
		$form->options['order_active']=array(array('value'=>'active','title'=>'решение активно'),
								array('value'=>'not_active','title'=>'решение не активно'));
		$order_id=$form->values['order_id'];
		$engine->tpl->set_tag('form', $form->render());
		$engine->tpl->set_tag('order_id',$order_id);
		echo $engine->tpl->render('std/page');
		exit;
	}
	$order_id=$form->values['order_id'];
	$order_date=strtotime($form->values['order_date']);
	$order_end_date=strtotime($form->values['order_end_date']);
	$order_active=$form->values['order_active']=='active'?1:0;
	$order_info=$engine->roskom->quote($form->values['order_info']);
	if ($form->values['order_end_date']==NULL)
	{
		$engine->roskom->query("REPLACE INTO roskom_orders (order_id,order_date,order_active,order_info)
							VALUES ($order_id,$order_date,$order_active,$order_info)"
					);
	}
	else
	{
				$engine->roskom->query("REPLACE INTO roskom_orders (order_id,order_date,order_end_date,order_active,order_info)
							VALUES ($order_id,$order_date,$order_end_date,$order_active,$order_info)"
					);
	}
	header("Location:" .$file_path ."orders.php");
}

if (isset($_POST['cancel']))
		header("Location:" .$file_path ."orders.php");
	
	$engine->tpl->set_tag('form', $form->render());
	
	echo $engine->tpl->render('std/page');
