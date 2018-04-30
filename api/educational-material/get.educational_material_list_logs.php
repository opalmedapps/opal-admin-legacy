<?php
	/* To get list logs on a particular educational material */
	include_once('educational-material.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serials = json_decode($_GET['serials']);

	$eduMat = new EduMaterial; // Object

	// Call function
	$educationalMaterialLogs = $eduMat->getEducationalMaterialListLogs($serials);

	// // Callback to http request
	print $callback.'('.json_encode($educationalMaterialLogs).')';

?>
