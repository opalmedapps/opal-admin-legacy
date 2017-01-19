<?php
	/* To get distinct test names */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$testResultObject = new TestResult; // Object

	// Call function
	$testNames = $testResultObject->getTestNames();

	// Callback to http request
	print $callback.'('.json_encode($testNames).')';

?>
