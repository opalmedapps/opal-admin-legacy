<?php
	/* To get a list of allowable content types*/
	include_once('educational-material.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$eduMat = new EduMaterial; // Object

	// Call function
	$contentTypes = $eduMat->getAllowableContentTypes();

	// Callback to http request
	print $callback.'('.json_encode($contentTypes).')';

?>
