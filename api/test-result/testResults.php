<?php
	/* To get a list of existing test results */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$testResult = new TestResult; // Object

	// Call function
	$existingTestResultList = $testResult->getExistingTestResults();

	// Callback to http request
	print $callback.'('.json_encode($existingTestResultList).')';

?>
