<?php

	/* To update crontab when this file is called from the command line */

	$cron = new CronControl; // Object

	// The argument pass is the cronSer
	$cronSer = $argv[1];
		
	// Call function
	$cron->updateCrontab($cronSer);
?>
