<?php
	/* To fetch the list of security questions */

    $currentFile = __FILE__; // Get location of this script

    // Find config file based on this location 
    $configFile = substr($currentFile, 0, strpos($currentFile, "opalAdmin")) . "opalAdmin/php/config.php";
	// Include config file 
	include_once($configFile);

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$patientObj = new Patient; // Object

	// Call function
	$securityQuestions = $patientObj->fetchSecurityQuestions();

	// Callback to http request
	print $callback.'('.json_encode($securityQuestions).')';

?>
