<?php
	/* To get logs on a particular notification */
	include_once('notification.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$notification = new Notification; // Object

	// Call function
	$notificationLogs = $notification->getNotificationLogs($serial);

	// Callback to http request
	print $callback.'('.json_encode($notificationLogs).')';

?>
