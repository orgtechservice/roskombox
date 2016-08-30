<?php
/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Удаление хоста из списка');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/del');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");
$engine->access->requireAccess("roskom:edit_access");

$file_path = $engine->config->read('site_prefix') ."admin/roskom/";
$table_post = isset($_GET['list'])?$_GET['list']:'';
$domain = isset($_GET['item'])?trim($_GET['item']):'';
$form = $engine->post->newForm('admin/roskom/del');
$form->values['list']=$table_post==1?'except':'manual';
$form->values['item']=$domain;

/**
*Удаляем запись
*/
if (isset($_POST['delete']))
{
	$form = $engine->post->parse('admin/roskom/del');
	$table_post=$form->values['list'];
	$domain=$form->values['item'];
	$where=$table_post."_domain_name=".$engine->roskom->quote($domain);
	$engine->roskom->delete("roskom_domain_$table_post",$where);
	header("Location:" .$file_path ."except.php");
}

if (isset($_POST['cancel']))
{
	$form = $engine->post->parse('admin/roskom/del');
	$table_post=$form->values['list'];
	$domain=$form->values['item'];
	header("Location:" .$file_path ."except.php");
}

$engine->tpl->set_tag('file_path', $file_path);
$engine->tpl->set_tag('list', $table_post);
$engine->tpl->set_tag('item',$domain);
$engine->tpl->set_tag('form', $form->render());
echo $engine->tpl->render('std/page');
