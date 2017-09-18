<?php
	/* To get a list of existing educational materials */
	include_once('educational-material.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$eduMat = new EduMaterial; // Object

	// Call function
	$existingEduMatList = $eduMat->getEducationalMaterials();

	// Callback to http request
	print $callback.'('.json_encode($existingEduMatList).')';

?>
