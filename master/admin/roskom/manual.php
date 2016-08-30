<?php

require dirname(__FILE__) . "/../system.php";

$operator = $engine->session->authorize();
$engine->access->requireAccess("roskom:view_access");
$engine->access->requireAccess("roskom:edit_access");

if(is_array(@ $_POST['domain'])) {
	header("Location: search.php");
	die();
}

$domain = $engine->roskom->quote(@ $_POST['domain']);
$rawdomain = @ $_POST['domain'];

switch(@ $_POST['action']) {
	case 'delete-block':
		$engine->roskom->exec("DELETE FROM roskom_domain_manual WHERE manual_domain_name = $domain");
		$engine->roskom->logManualOperation("Deleted explicit block for $rawdomain", 'warning', $operator['user_id']);
	break;
	case 'add-block':
		$engine->roskom->exec("REPLACE INTO roskom_domain_manual (manual_domain_name) VALUES ($domain)");
		$engine->roskom->logManualOperation("Added explicit block for $rawdomain", 'warning', $operator['user_id']);
	break;
	case 'delete-exception':
		$engine->roskom->exec("DELETE FROM roskom_domain_except WHERE except_domain_name = $domain");
		$engine->roskom->logManualOperation("Deleted explicit exception for $rawdomain", 'warning', $operator['user_id']);
	break;
	case 'add-exception':
		$engine->roskom->exec("REPLACE INTO roskom_domain_except (except_domain_name) VALUES ($domain)");
		$engine->roskom->logManualOperation("Added explicit exception for $rawdomain", 'warning', $operator['user_id']);
	break;
}

header('Location: search.php?domain=' . urlencode($rawdomain));
