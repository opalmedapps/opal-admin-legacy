<?php
	/* To get cron logs for highcharts */
	include_once('cron.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];

	$cron = new Cron; // Object

	// Call function
	$cronLogs = $cron->getCronChartLogs();

	// Callback to http request
	print $callback.'('.json_encode($cronLogs).')';

?>
