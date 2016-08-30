<?php
/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Удаление url-адреса из списка');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/url_del');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");
$engine->access->requireAccess("roskom:edit_access");

$file_path = $engine->config->read('site_prefix') ."admin/roskom/";
$order_id = intval(trim($_GET['id']));
$url_text = trim($_GET['name']);
/**
*Создаем форму
*/
$form = $engine->post->newForm('admin/roskom/url_del');
$form->values['order_id']=$order_id;
$form->values['url_text']=$url_text;

/**
*Удаляем запись
*/
if (isset($_POST['delete']))
{
	$form = $engine->post->parse('admin/roskom/url_del');
	$order_id = $form->values['order_id'];
	$url_text = $form->values['url_text'];
	$where='url_order_id='.$order_id.' AND url_text='.$engine->roskom->quote($url_text);
	$engine->roskom->delete('roskom_order_url',$where);
	header("Location:" .$file_path ."url.php?id=$order_id");
}

if (isset($_POST['cancel']))
{
	$form = $engine->post->parse('admin/roskom/url_del');
	$order_id = $form->values['order_id'];
	$url_text = $form->values['url_text'];
	header("Location:" .$file_path ."url.php?id=$order_id");
}

$engine->tpl->set_tag('file_path', $file_path);
$engine->tpl->set_tag('order_id', $order_id);
$engine->tpl->set_tag('url_text',$url_text);
$engine->tpl->set_tag('form', $form->render());
echo $engine->tpl->render('std/page');
