<?php

	/* To delete an educational material */

    $currentFile = __FILE__; // Get location of this script

    // Find config file based on this location 
    $configFile = substr($currentFile, 0, strpos($currentFile, "opalAdmin")) . "opalAdmin/php/config.php";
	// Include config file 
	include_once($configFile);

	$eduMat = new EduMaterial; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $eduMat->removeEducationalMaterial($serial);
    print json_encode($response); // Return response

?>
