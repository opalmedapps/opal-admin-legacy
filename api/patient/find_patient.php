<?php

	/* Find patient given an SSN */

    $currentFile = __FILE__; // Get location of this script

    // Find config file based on this location 
    $configFile = substr($currentFile, 0, strpos($currentFile, "opalAdmin")) . "opalAdmin/php/config.php";
	// Include config file 
	include_once($configFile);

	// Retrieve FORM param
	$callback = $_GET['callback'];
	$ssn = $_GET['ssn'];

	$patientObj = new Patient; // Object

	// Call function
	$patientResponse = $patientObj->findPatient($ssn);

	// Callback to http request
	print $callback.'('.json_encode($patientResponse).')';

?>
