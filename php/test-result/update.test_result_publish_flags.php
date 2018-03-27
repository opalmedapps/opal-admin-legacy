<?php 

	/* To call Test Result Object to update when the "Publish Flag" checkbox has been changed */
	include_once('test-result.inc');

	$testResultObject = new TestResult; // Object

	// Retrieve FORM param
	$testResultPublishes	= $_POST['publishList'];
	$user 					= $_POST['user'];
	
	// Construct array
	$testResultList = array();

	foreach($testResultPublishes as $testResult) {
		array_push($testResultList, array('serial' => $testResult['serial'], 'publish' => $testResult['publish']));
	}

	// Call function
    $response = $testResultObject->updatePublishFlags($testResultList, $user);
    print json_encode($response); // Return response
?>


