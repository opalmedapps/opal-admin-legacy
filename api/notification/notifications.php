<?php
	/* To get a list of existing notification */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$notification = new Notification; // Object

	// Call function
	$existingNotificationList = $notification->getNotifications();

	// Callback to http request
	print $callback.'('.json_encode($existingNotificationList).')';

?>
