<?php
	/* To get a list of phase in treatments */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$eduMat = new EduMaterial; // Object

	// Call function
	$phases = $eduMat->getPhaseInTreatments();

	// Callback to http request
	print $callback.'('.json_encode($phases).')';

?>
