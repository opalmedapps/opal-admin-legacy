<?php

	/* To delete an educational material */

	$testResult = new TestResult; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $testResult->removeTestResult($serial);
    print json_encode($response); // Return response

?>
