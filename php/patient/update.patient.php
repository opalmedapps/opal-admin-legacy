<?php 

	/* To update a patient */
	include_once('patient.inc');

	// Construct array from FORM params
	$patientArray = array(
		'password'			=> $_POST['password'],
		'serial'			=> $_POST['serial']
	);

	$patientObj = new Patient; // Object

	// Call function
	$response = $patientObj->updatePatient($patientArray);
	print json_encode($response);

	
?>


