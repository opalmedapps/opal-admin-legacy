<?php

	/* Find patient given an SSN */
	include_once('patient.inc');

	// Retrieve FORM param
	$callback 	= $_GET['callback'];
	$ssn 		= $_GET['ssn'];
	$id  		= $_GET['id'];

	$patientObj = new Patient; // Object

	// Call function
	$patientResponse = $patientObj->findPatient($ssn, $id);

	// Callback to http request
	print $callback.'('.json_encode($patientResponse).')';

?>
