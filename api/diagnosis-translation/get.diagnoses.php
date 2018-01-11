<?php
	/* To get a list of distinct diagnosis codes */
	include_once('diagnosis-translation.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$diagnosisObject = new Diagnosis; // Object

	// Call function
	$diagnoses = $diagnosisObject->getDiagnoses();

	// Callback to http request
	print $callback.'('.json_encode($diagnoses).')';

?>