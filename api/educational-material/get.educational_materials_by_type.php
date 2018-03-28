<?php
	/* To get list of educational material by content type */
	include_once('educational-material.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$contentType = $_GET['type'];

	$eduMat = new EduMaterial; // Object

	// Call function
	$eduMatList = $eduMat->getEducationalMaterialsByType($contentType);

	// Callback to http request
	print $callback.'('.json_encode($eduMatList).')';

?>
