<?php
	/* To get details on a particular educational material */

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$eduMat = new EduMaterial; // Object

	// Call function
	$eduMatDetails = $eduMat->getEducationalMaterialDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($eduMatDetails).')';

?>
