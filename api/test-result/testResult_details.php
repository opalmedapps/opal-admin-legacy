<?php
	/* To get details on a particular test result */

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$testResult = new TestResult; // Object

	// Call function
	$testResultDetails = $testResult->getTestResultDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($testResultDetails).')';

?>
