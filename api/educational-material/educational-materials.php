<?php
	/* To get a list of existing educational materials */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$eduMat = new EduMaterial; // Object

	// Call function
	$existingEduMatList = $eduMat->getExistingEducationalMaterials();

	// Callback to http request
	print $callback.'('.json_encode($existingEduMatList).')';

?>
