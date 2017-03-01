<?php

	/* Determine if email is taken */
	include_once('patient.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];
	$email = $_GET['email'];

	$patientObj = new Patient; // Object

	// Call function
	$Response = $patientObj->emailAlreadyInUse($email);

	// Callback to http request
	print $callback.'('.json_encode($Response).')';

?>
