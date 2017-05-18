<?php

	/* Get patient details */
	include_once('patient.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$patientObj = new Patient; // Object

	// Call function
	$patientDetails = $patientObj->getPatientDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($patientDetails).')';

?>