<?php
	/* To get list logs on a particular notification */
	include_once('notification.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serials = json_decode($_GET['serials']);

	$notification = new Notification; // Object

	// Call function
	$notificationLogs = $notification->getNotificationListLogs($serials);

	// // Callback to http request
	print $callback.'('.json_encode($notificationLogs).')';

?>
