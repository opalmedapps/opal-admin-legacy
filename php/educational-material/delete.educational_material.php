<?php

	/* To delete an educational material */
	include_once('educational-material.inc');

	$eduMat = new EduMaterial; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];
	$user = $_POST['user'];

	// Call function
    $response = $eduMat->deleteEducationalMaterial($serial, $user);
    print json_encode($response); // Return response

?>
