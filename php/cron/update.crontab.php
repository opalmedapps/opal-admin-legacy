<?php

	/* To update crontab when this file is called from the command line */
	include_once('cron.inc');

	$cron = new Cron; // Object

	// The argument pass is the cronSer
	$cronSer = $argv[1];
		
	// Call function
	$cron->updateCrontab($cronSer);
?>
