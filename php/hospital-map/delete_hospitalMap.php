<?php

	/* To delete a hospital map */

	$hosMap = new HospitalMap; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
	$hosMap->removeHospitalMap($serial);

?>
