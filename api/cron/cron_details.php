<?php 
	/* To get details on a cron profile */

	// Retrieve FORM params
	$callback = $_GET['callback'];

	$cron = new CronControl; // Object

	// Call function
	$cronDetails = $cron->getCronDetails();

	// Callback to http request
	print $callback.'('.json_encode($cronDetails).')';

?>
