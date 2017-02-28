<?php 

	/* To update cron with new information */
	include_once('cron.inc');

	$cron = new CronControl; // Object

	// Retrieve FORM params
	$nextCronDate 	= $_POST['nextCronDate'];
	$repeatUnits	= $_POST['repeatUnits'];
	$nextCronTime	= $_POST['nextCronTime'];
	$repeatInterval	= $_POST['repeatInterval'];

	// Contruct array
	$cronArray	= array(
		'nextCronDate' 	=> $nextCronDate, 
		'repeatUnits' 	=> $repeatUnits, 
		'nextCronTime' 	=> $nextCronTime, 
		'repeatInterval'=> $repeatInterval
	);

	// Call function
	$cron->updateCron($cronArray);

?>
