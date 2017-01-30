<?php
	/* To get details on a particular hospital map */

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$hosMap = new HospitalMap; // Object

	// Call function
	$hosMapDetails = $hosMap->getHospitalMapDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($hosMapDetails).')';

?>
