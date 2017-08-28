<?php

	/* To delete a user */
	include_once('user.inc');

	$user = new Users; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $user->deleteUser($serial);
    print json_encode($response); // Return response

?>