<?php

	/* To delete a hospital map */
	include_once('hospital-map.inc');

	$hosMap = new HospitalMap; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];
	$user 	= $_POST['user'];

	// Call function
	$hosMap->deleteHospitalMap($serial, $user);

?>
