<?php
	/* To get a list of existing test result groups */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$testResult = new TestResult; // Object

	// Call function
	$groups = $testResult->getTestResultGroups();

	// Callback to http request
	print $callback.'('.json_encode($groups).')';

?>
