<?php
	/* To get a list of existing test result groups */
	include_once('test-result.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$testResult = new TestResult; // Object

	// Call function
	$groups = $testResult->getTestResultGroups();

	// Callback to http request
	print $callback.'('.json_encode($groups).')';

?>
