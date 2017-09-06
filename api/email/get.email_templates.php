<?php

	/* To get a list of existing email templates */

	include_once('email.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$emailObj = new Email; // Object

	// Call function
	$existingEmailList = $emailObj->getEmailTemplates();

	// Callback to http request
	print $callback.'('.json_encode($existingEmailList).')';

?>
