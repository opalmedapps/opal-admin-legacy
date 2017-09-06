<?php

	/* To delete a notification */
	include_once('notification.inc');

	$notification = new Notification; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
	$response = $notification->deleteNotification($serial);
    print json_encode($response); // Return response
?>
