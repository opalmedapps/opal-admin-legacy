<?php 

	/* To insert a newly created patient user */

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


