<?php

	/* To delete a notification */
	include_once('notification.inc');

	$notification = new Notification; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];
	$user 	= $_POST['user'];

	// Call function
	$response = $notification->deleteNotification($serial, $user);
    print json_encode($response); // Return response
?>
