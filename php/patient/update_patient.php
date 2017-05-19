<?php 

	/* To insert a newly created patient user */
	include_once('patient.inc');

	// Construct array
	$patientArray = array(
		'password'			=> $_POST['password'],
		'serial'			=> $_POST['serial']
	);

	$patientObj = new Patient; // Object

	// Call function
	$response = $patientObj->updatePatient($patientArray);
	print json_encode($response);

	
?>


