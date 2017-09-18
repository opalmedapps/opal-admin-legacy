<?php
	/* To get a list of existing patients */
	include_once('patient.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$patient = new Patient; // Object

	// Call function
	$existingPatientList = $patient->getPatients();

	// Callback to http request
	print $callback.'('.json_encode($existingPatientList).')';

?>
