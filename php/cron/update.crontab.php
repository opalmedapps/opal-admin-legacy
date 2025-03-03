<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/* To update crontab when this file is called from the command line */
	include_once('cron.inc');

	$cron = new Cron; // Object

	// The argument pass is the cronSer
	$cronSer = $argv[1];
		
	// Call function
	$cron->updateCrontab($cronSer);
?>
