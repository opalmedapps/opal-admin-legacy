<?php
	/* To get details on a particular diagnosis translation */
	include_once('diagnosis-translation.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$Diagnosis = new Diagnosis; // Object

	// Call function
	$diagnosisTranslationDetails = $Diagnosis->getDiagnosisTranslationDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($diagnosisTranslationDetails).')';

?>