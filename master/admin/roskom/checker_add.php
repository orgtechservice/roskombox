<?php

/**
 * Подключаем двигало
 */
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Добавление проверялки');
$engine->tpl->set_tag('CONTENT', 'std/form');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");
$engine->access->requireAccess("roskom:edit_access");

if(isset($_POST['save'])) {
	$form = $engine->post->parse('admin/roskom/checker_add');
	$engine->roskom->validateCheckerForm($form);
	$engine->common->showAgainIfHasErrors($form);

	$data = [
		'checker_ip' => $form->values['ip'],
		'checker_description' => $form->values['description'],
		'checker_max_available' => $form->values['max_available'],
		'checker_shortname' => $form->values['shortname'],
	];

	$engine->roskom->insertQuoted('roskom_checkers', $data);

	// Добавим запись в системный журнал
	$checker_id = $engine->roskom->lastInsertId();
	$engine->syslog->put('roskom', "Добавлена проверялка: checker#{$checker_id}", false, $operator);

	// Всё готово, перенаправим пользователя при помощи страницы «ok»
	$engine->common->done('checkers.php');
}

$form = $engine->post->newForm('admin/roskom/checker_add');

$engine->tpl->set_tag('form', $form->render());

echo $engine->tpl->render('std/page');
