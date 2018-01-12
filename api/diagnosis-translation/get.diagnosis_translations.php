<?php
	/* To get a list of existing diagnosis translations */
	include_once('diagnosis-translation.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$Diagnosis = new Diagnosis; // Object

	// Call function
	$existingDiagnosisTranslationList = $Diagnosis->getExistingDiagnosisTranslations();

	// Callback to http request
	print $callback.'('.json_encode($existingDiagnosisTranslationList).')';

?>