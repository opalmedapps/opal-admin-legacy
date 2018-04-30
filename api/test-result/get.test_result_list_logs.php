<?php
	/* To get list logs on a particular test result */
	include_once('test-result.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serials = json_decode($_GET['serials']);

	$testResult = new TestResult; // Object

	// Call function
	$testResultLogs = $testResult->getTestResultListLogs($serials);

	// // Callback to http request
	print $callback.'('.json_encode($testResultLogs).')';

?>
