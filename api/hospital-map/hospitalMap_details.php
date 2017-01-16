<?php
	/* To get details on a particular hospital map */

    $currentFile = __FILE__; // Get location of this script

    // Find config file based on this location 
    $configFile = substr($currentFile, 0, strpos($currentFile, "opalAdmin")) . "opalAdmin/php/config.php";
	// Include config file 
	include_once($configFile);

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$hosMap = new HospitalMap; // Object

	// Call function
	$hosMapDetails = $hosMap->getHospitalMapDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($hosMapDetails).')';

?>
