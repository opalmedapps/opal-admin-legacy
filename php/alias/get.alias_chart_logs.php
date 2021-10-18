<?php
	header('Content-Type: application/javascript');
	/* To get logs on a particular alias for highcharts */
	include_once('alias.inc');

	// Retrieve FORM params
	$callback = $_POST['callback'];
	$serial = ( $_POST['serial'] === 'undefined' ) ? null : $_POST['serial'];
	$type = ( $_POST['type'] === 'undefined' ) ? null : $_POST['type'];

	$alias = new Alias; // Object

	// Call function
	$aliasLogs = $alias->getAliasChartLogs($serial, $type);

	// Callback to http request
	echo json_encode($aliasLogs);

?>
