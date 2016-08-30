<?php

require dirname(__FILE__) . "/../system.php";

$engine->tpl->set_tag('TITLE', 'Журнал автоматических выгрузок');
$engine->tpl->set_tag('CONTENT', 'admin/roskom/log');
$engine->tpl->set_tag('MODULE', basename(__DIR__));

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");

$start = $engine->common->dateToUnix(@ $_GET['s_day']);
$end = $engine->common->dateToUnix(@ $_GET['e_day'], true);
$type = in_array($type = @ $_GET['type'], ['any', 'info', 'warning', 'error']) ? $type : 'any';

$engine->tpl->set_tag('start', $engine->common->formatDate($start, true));
$engine->tpl->set_tag('end', $engine->common->formatDate($end, true));
$engine->tpl->set_tag('type', $type);

$extra_where = ($type == 'any') ? '' : " AND log_type = '{$type}'";

$entries = [];

/*
$statement = $engine->db->query("SELECT syslog_time AS log_time, syslog_message AS log_message, u.* FROM (syslog AS l LEFT JOIN users AS u ON u.user_id = l.syslog_operator_id) WHERE syslog_module = 'roskom' AND (UNIX_TIMESTAMP(syslog_time) BETWEEN $start AND $end)$extra_where ORDER BY syslog_time DESC");
while($row = $engine->roskom->fetchAssoc($statement)) {
	$matches = [];
	if(preg_match('#^\[([^\]])\] \([^\)]\) (.*)#', $row['log_message'], $matches)) {
		$type = $matches[1];
		$message = $matches[3];
	} else {
		$type = 'info';
		$message = $row['log_message'];
	}

	$entries[] = [
		'time' => $engine->common->formatDate($row['log_time']),
		'type' => $type,
		'message' => $message,
		'user' => $row['user_login'],
		'user_fio' => $row['user_fio'],
		'script' => $row['log_script_name']
	];
}
*/
$statement = $engine->roskom->query("SELECT log_time, log_message, log_type, log_script_name FROM roskom_log AS l WHERE (log_time BETWEEN $start AND $end)$extra_where ORDER BY log_id DESC");
while($row = $engine->roskom->fetchAssoc($statement)) {
	$entries[] = ['time' => $engine->common->formatDate($row['log_time']), 'type' => $row['log_type'], 'message' => $row['log_message'], 'script' => $row['log_script_name']];
}
$engine->roskom->freeResult($statement);

$engine->tpl->set_tag('entries', $entries);

echo $engine->tpl->render('std/page');
