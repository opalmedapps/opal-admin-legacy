<?php

// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
	/* To update source database enabled flags if any changes */
	include_once('application.inc');

	$applicationObj = new Application; // Object

	// Call function
  $response = $applicationObj->updateSourceDatabases($_POST);

  print json_encode($response); // Return response

?>
