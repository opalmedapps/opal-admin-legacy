<?php
	/* To get a list of existing educational material types */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$eduMat = new EduMaterial; // Object

	// Call function
	$types = $eduMat->getEducationalMaterialTypes();

	// Callback to http request
	print $callback.'('.json_encode($types).')';

?>
