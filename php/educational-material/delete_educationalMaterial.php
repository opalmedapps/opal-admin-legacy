<?php

	/* To delete an educational material */

	$eduMat = new EduMaterial; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $eduMat->removeEducationalMaterial($serial);
    print json_encode($response); // Return response

?>
