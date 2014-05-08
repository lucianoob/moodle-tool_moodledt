<?php
	header('HTTP/1.0 403 Forbidden');

	require_once('../../../../config.php');

	$PAGE->set_url($_SERVER['PHP_SELF']);
	$PAGE->set_pagelayout('admin');
	$PAGE->set_context(context_system::instance());
	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('error'));
	echo '<center>'.get_string('nopermissiontoshow', 'core_error').'</center>';
?>