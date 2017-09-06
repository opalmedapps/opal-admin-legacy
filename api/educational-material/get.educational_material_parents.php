<?php
	/* To get a list of existing educational material parents */
	include_once('educational-material.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$eduMat = new EduMaterial; // Object

	// Call function
	$parents = $eduMat->getParentEducationalMaterials();

	// Callback to http request
	print $callback.'('.json_encode($parents).')';

?>
