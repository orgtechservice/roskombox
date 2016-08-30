<?php

require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

$checker_id = (int) @ $_REQUEST['id'];

$engine->roskom->begin();
try {
	$checker = $engine->roskom->queryOne("SELECT * FROM roskom_checkers WHERE checker_id = $checker_id FOR UPDATE");

	if(!$checker) {
		$engine->roskom->rollback();
		$engine->common->notFound();
	}

	// А что если проверка уже началась, пока пользователь размышлял
	if($checker['checker_state'] == 'scanning' || $checker['checker_force_scan']) {
		// Просто покажем 404
		$engine->roskom->rollback();
		$engine->common->notFound();
	}

	$engine->roskom->update('roskom_checkers', ['checker_force_scan' => '1'], "checker_id = $checker_id");

	$engine->roskom->commit();
	$engine->common->done("checker.php?id=$checker_id");
} catch(Exception $e) {
	$engine->roskom->rollback();
	$engine->common->notFound();
}
