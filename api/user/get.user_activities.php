<?php
	/* To get a list of user activities */
	include_once('user.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$user = new Users; // Object

	// Call function
	$userActivityList = $user->getUserActivities();

	// Callback to http request
	print $callback.'('.json_encode($userActivityList).')';

?>
