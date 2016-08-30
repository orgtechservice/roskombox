<?php

/**
* Подключаем двигало
*/
require dirname(__FILE__) . "/system.php";

$engine->tpl->set_tag('TITLE', 'Авторизация');
$engine->tpl->set_tag('CONTENT', 'std/form');

if ( isset($_POST['action']) )
{
	$form = $engine->post->parse('admin/login');
	
	$user = $engine->db->selectOne("users", "*", "user_login = " . $engine->db->quote($form->values["login"]));
	
	if ( empty($user) )
	{
		$form->error('std:user_not_found', $form->values['login']);
	}
	elseif ( $user['user_status'] !== 'active' )
	{
		$form->error('std:user_is_locked', $form->values['login']);
	}
	elseif ( strtolower($user['user_passwd']) !== strtolower(md5($form->values['password'])) )
	{
		$form->error('std:user_wrong_password', $form->values['login']);
	}
	
	if ( $form->hasErrors() )
	{
		if ( $form->values['return'] && $engine->ticket->verify($form->values['ticket'], $form->values['return']) )
		{
			$form->values['ticket'] = $engine->ticket->sign(3600, $form->values['return']);
		}
		$engine->tpl->set_tag('form', $form->render());
		echo $engine->tpl->render('std/page');
		exit;
	}
	
	$engine->session->start($user, $form->values['autologin']);

	$ip_address = $_SERVER['REMOTE_ADDR'];
	$engine->syslog->put('auth', "Успешная авторизация пользователя {$user['user_login']} (ID {$user['user_id']}, IP {$ip_address})", false, $user);
	
	if ( $form->values['return'] && $engine->ticket->verify($form->values['ticket'], $form->values['return']) )
	{
		$return = $form->values['return'];
	}
	else
	{
		$return = $engine->config->read('site_prefix') . "admin/lan/index.php";
	}
	
	header('Location: ' . $return);
	$engine->common->done($return);
}

$form = $engine->post->newForm('admin/login');
$engine->tpl->set_tag('form', $form->render());
echo $engine->tpl->render('std/page');
