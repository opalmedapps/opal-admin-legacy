<?php
	/* To get cron logs for highcharts */
	include_once('cron.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$contents = json_decode($_GET['contents'], true);

	$cron = new Cron; // Object

	// Call function
	$cronLogs = $cron->getCronListLogs($contents);

	// Callback to http request
	print $callback.'('.json_encode($cronLogs).')';

?>
