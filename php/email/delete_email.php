<?php

	/* To delete an email */
	include_once('email.inc');

	$email = new Email; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $email->removeEmail($serial);

    print json_encode($response); // Return response

?>
