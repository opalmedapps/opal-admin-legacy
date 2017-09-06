<?php 

	/* To update cron with new information */
	include_once('cron.inc');

	$cron = new Cron; // Object

	// Retrieve FORM params
	$nextCronDate 	= $_POST['nextCronDate'];
	$repeatUnits	= $_POST['repeatUnits'];
	$nextCronTime	= $_POST['nextCronTime'];
	$repeatInterval	= $_POST['repeatInterval'];

	// Construct array
	$cronArray	= array(
		'nextCronDate' 	=> $nextCronDate, 
		'repeatUnits' 	=> $repeatUnits, 
		'nextCronTime' 	=> $nextCronTime, 
		'repeatInterval'=> $repeatInterval
	);

	// Call function
	$cron->updateCron($cronArray);

?>
