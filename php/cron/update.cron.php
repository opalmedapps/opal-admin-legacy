<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

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
