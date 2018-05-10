<?php

	/* To delete a test result */
	include_once('test-result.inc');

	$testResult = new TestResult; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];
	$user = $_POST['user'];

	// Call function
    $response = $testResult->deleteTestResult($serial, $user);
    print json_encode($response); // Return response

?>
