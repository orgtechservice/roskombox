<?php

/**
 * Подключаем двигало
 */
require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Удаление проверялки');
$engine->tpl->set_tag('CONTENT', 'std/form');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");
$engine->access->requireAccess("roskom:edit_access");


$checker_id = (int) @ $_REQUEST['id'];
$checker = $engine->roskom->checkerById($checker_id);
if(!$checker) $engine->common->notFound();

if(isset($_POST['proceed'])) {
	$form = $engine->post->parse('admin/roskom/checker_del');
	$engine->common->showAgainIfHasErrors($form);
	$engine->roskom->exec("DELETE FROM roskom_checkers WHERE checker_id = $checker_id");
	$engine->common->done('checkers.php');
}

$form = $engine->post->newForm('admin/roskom/checker_del');
$form->values['id'] = $checker_id;
$form->values['question'] = "ID: {$checker_id}, IP: {$checker['checker_ip']}, описание: «{$checker['checker_description']}»";

$engine->tpl->set_tag('form', $form->render());

echo $engine->tpl->render('std/page');
