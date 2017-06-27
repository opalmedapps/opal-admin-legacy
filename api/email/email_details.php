<?php

	/* To get details of a particular email template */

	include_once('email.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$emailObj = new Email; // Object

	// Call function
	$emailDetails = $emailObj->getEmailDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($emailDetails).')';

?>
