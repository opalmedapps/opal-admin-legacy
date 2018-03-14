<?php

	/* To delete a diagnosis translation */
	include_once('diagnosis-translation.inc');

	$Diagnosis = new Diagnosis; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];
	$user = $_POST['user'];

	// Call function
    $response = $Diagnosis->deleteDiagnosisTranslation($serial, $user);
    print json_encode($response); // Return response

?>