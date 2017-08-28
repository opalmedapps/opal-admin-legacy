<?php
	/* To fetch the list of security questions */
	include_once('patient.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];
	$language = $_GET['lang'];

	$patientObj = new Patient; // Object

	// Call function
	$securityQuestions = $patientObj->getSecurityQuestions($language);

	// Callback to http request
	print $callback.'('.json_encode($securityQuestions).')';

?>
