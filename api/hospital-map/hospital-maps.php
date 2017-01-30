<?php
	/* To get a list of existing hospital maps */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$hosMap = new HospitalMap; // Object

	// Call function
	$existingHosMapList = $hosMap->getHospitalMaps();

	// Callback to http request
	print $callback.'('.json_encode($existingHosMapList).')';

?>
