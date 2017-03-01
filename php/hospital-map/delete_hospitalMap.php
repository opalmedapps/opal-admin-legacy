<?php

	/* To delete a hospital map */
	include_once('hospital-map.inc');

	$hosMap = new HospitalMap; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
	$hosMap->removeHospitalMap($serial);

?>
