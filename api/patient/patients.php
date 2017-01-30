<?php
	/* To get a list of existing patients */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$patient = new Patient; // Object

	// Call function
	$existingPatientList = $patient->getExistingPatients();

	// Callback to http request
	print $callback.'('.json_encode($existingPatientList).')';

?>
