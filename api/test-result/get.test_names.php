<?php
	/* To get a list of distinct test names */
	include_once('test-result.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$testResultObject = new TestResult; // Object

	// Call function
	$testNames = $testResultObject->getTestNames();

	// Callback to http request
	print $callback.'('.json_encode($testNames).')';

?>
