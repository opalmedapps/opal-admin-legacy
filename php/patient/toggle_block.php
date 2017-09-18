<?php 

	/* To set block status in the database */
	include_once('patient.inc');

	// Construct array from FORM params
	$patientArray = array(
		'disabled'			=> $_POST['disabled'],
		'serial'			=> $_POST['serial'],
		'uid'				=> $_POST['uid'],
		'reason'			=> $_POST['reason']
	);

	$patientObj = new Patient; // Object

	// Call function
	$response = $patientObj->toggleBlock($patientArray);
	print json_encode($response);

	
?>