<?php

	/* To delete an email */
	include_once('email.inc');

	$email = new Email; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];
	$user = $_POST['user'];

	// Call function
    $response = $email->deleteEmail($serial, $user);

    print json_encode($response); // Return response

?>
