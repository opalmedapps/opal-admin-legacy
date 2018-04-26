<?php
	/* To get logs on a particular test result for highcharts */
	include_once('test-result.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = ( $_GET['serial'] === 'undefined' ) ? null : $_GET['serial'];

	$testResult = new TestResult; // Object

	// Call function
	$testResultLogs = $testResult->getTestResultChartLogs($serial);

	// Callback to http request
	print $callback.'('.json_encode($testResultLogs).')';

?>
