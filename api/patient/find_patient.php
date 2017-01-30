<?php

	/* Find patient given an SSN */

	// Retrieve FORM param
	$callback = $_GET['callback'];
	$ssn = $_GET['ssn'];

	$patientObj = new Patient; // Object

	// Call function
	$patientResponse = $patientObj->findPatient($ssn);

	// Callback to http request
	print $callback.'('.json_encode($patientResponse).')';

?>
