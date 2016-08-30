<?php

require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Проверялка');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/checker_edit');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

$checker_id = (int) @ $_REQUEST['id'];
$checker = $engine->roskom->checkerById($checker_id);
if(!$checker) $engine->common->notFound();

$engine->tpl->set_tag('TITLE', "Проверялка: {$checker['checker_ip']}");
$engine->tpl->set_tag('checker', $checker);

if(isset($_POST['save'])) {
	$form = $engine->post->parse('admin/roskom/checker_edit');
	$engine->common->showAgainIfHasErrors($form);
	$data = [
		'checker_description' => $engine->roskom->quote($form->values['description']),
		'checker_max_available' => $engine->roskom->quote($form->values['max_available']),
		'checker_shortname' => $engine->roskom->quote($form->values['shortname']),
	];
	$engine->roskom->update('roskom_checkers', $data, "checker_id = $checker_id");
	$engine->common->done("checker.php?id=$checker_id");
}

$form = $engine->post->newForm('admin/roskom/checker_edit');
$form->values['id'] = $checker_id;
$form->values['description'] = $checker['checker_description'];
$form->values['shortname'] = $checker['checker_shortname'];
$form->values['max_available'] = $checker['checker_max_available'];

$engine->tpl->set_tag('form', $form->render());

echo $engine->roskom->renderCheckerTabs($checker, "admin/roskom/checker_edit.php?id=$checker_id");
