<?php 

	/* To call Patient Object to update patient when the "Transfer Flag" checkbox has been changed */
	include_once('patient.inc');

	$patientObject = new Patient; // Object

	// Retrieve FORM params
	$patientTransfers	= $_POST['transferList'];
	
	// Construct array
	$patientList = array();

	foreach($patientTransfers as $patient) {
		array_push($patientList, array('serial' => $patient['serial'], 'transfer' => $patient['transfer']));
	}

	// Call function
	$patientObject->updatePatientTransferFlags($patientList);
?>


