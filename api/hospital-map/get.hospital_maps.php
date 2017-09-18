<?php
	/* To get a list of existing hospital maps */
	include_once('hospital-map.inc');
 
	// Retrieve FORM param
	$callback = $_GET['callback'];

	$hosMap = new HospitalMap; // Object

	// Call function
	$existingHosMapList = $hosMap->getHospitalMaps();

	// Callback to http request
	print $callback.'('.json_encode($existingHosMapList).')';

?>
