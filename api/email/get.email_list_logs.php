<?php
	/* To get list logs on a particular email */
	include_once('email.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serials = json_decode($_GET['serials']);

	$email = new Email; // Object

	// Call function
	$emailLogs = $email->getEmailListLogs($serials);

	// // Callback to http request
	print $callback.'('.json_encode($emailLogs).')';

?>
