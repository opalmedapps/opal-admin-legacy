<?php

	/* To delete a test result */
	include_once('test-result.inc');

	$testResult = new TestResult; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $testResult->deleteTestResult($serial);
    print json_encode($response); // Return response

?>
