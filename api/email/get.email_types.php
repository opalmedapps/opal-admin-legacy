<?php

	/* To get a list email types */

	include_once('email.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$emailObj = new Email; // Object

	// Call function
	$types = $emailObj->getEmailTypes();

	// Callback to http request
	print $callback.'('.json_encode($types).')';

?>
