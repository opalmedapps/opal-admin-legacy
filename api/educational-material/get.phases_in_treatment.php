<?php
	/* To get a list of phase in treatments */
	include_once('educational-material.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$eduMat = new EduMaterial; // Object

	// Call function
	$phases = $eduMat->getPhasesInTreatment();

	// Callback to http request
	print $callback.'('.json_encode($phases).')';

?>
