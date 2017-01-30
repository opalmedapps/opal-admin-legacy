<?php

	/* To delete a notification */

	$notification = new Notification; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
	$response = $notification->removeNotification($serial);
    print json_encode($response); // Return response
?>
