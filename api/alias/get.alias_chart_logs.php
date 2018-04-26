<?php
	/* To get logs on a particular alias for highcharts */
	include_once('alias.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = ( $_GET['serial'] === 'undefined' ) ? null : $_GET['serial'];
	$type = ( $_GET['type'] === 'undefined' ) ? null : $_GET['type'];

	$alias = new Alias; // Object

	// Call function
	$aliasLogs = $alias->getAliasChartLogs($serial, $type);

	// Callback to http request
	print $callback.'('.json_encode($aliasLogs).')';

?>
