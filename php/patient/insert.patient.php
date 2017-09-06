<?php 

	/* To insert a newly created patient user */
	include_once('patient.inc');

	// Construct array from FORM params
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
		'accessLevel'		=> $_POST['accessLevel'],
		'data'				=> $_POST['data']
	);

	$patientObj = new Patient; // Object

	// Call function
	$response = $patientObj->registerPatient($patientArray);
	print json_encode($response); // return response

	
?>


