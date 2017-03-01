<?php
	/* To get a list of *unused* notification types */
	include_once('notification.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$notification = new Notification; // Object

	// Call function
	$types = $notification->getNotificationTypes();

	// Callback to http request
	print $callback.'('.json_encode($types).')';

?>
