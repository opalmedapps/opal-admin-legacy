<?php 

	/* To insert a newly created patient user */

    $currentFile = __FILE__; // Get location of this script

    // Find config file based on this location 
    $configFile = substr($currentFile, 0, strpos($currentFile, "ATO")) . "ATO/php/config.php";
	// Include config file 
	include_once($configFile);

	// Construct array
	$patientArray = array(
		'email'				=> $_POST['email'],
		'password'			=> $_POST['password'],
		'language'			=> $_POST['language'],
		'uid'				=> $_POST['uid'],
		'securityQuestion1'	=> $_POST['securityQuestion1'],
		'securityQuestion2'	=> $_POST['securityQuestion2'],
		'securityQuestion3'	=> $_POST['securityQuestion3'],
		'cellNum'			=> $_POST['cellNum'],
		'SSN'				=> $_POST['SSN'],
		'data'				=> $_POST['data']
	);

	$patientObj = new Patient; // Object

	// Call function
	$patientObj->registerPatient($patientArray);

	
?>


