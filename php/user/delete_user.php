<?php

	/* To delete a user */

	$user = new Users; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $user->removeUser($serial);
    print json_encode($response); // Return response

?>