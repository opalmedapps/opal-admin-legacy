<?php
	/* To get details on a particular notification */

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$notification = new Notification; // Object

	// Call function
	$notificationDetails = $notification->getNotificationDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($notificationDetails).')';

?>
