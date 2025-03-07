<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
	/* To call Edu Material Object to update when the "Publish Flag" checkbox has been changed */
	include_once('educational-material.inc');

	$eduMatObject = new EduMaterial; // Object

	// Retrieve FORM params
	$eduMatPublishes	= $_POST['publishList'];
	$user							= $_POST['user'];

	// Construct array
	$eduMatList = array();

	foreach($eduMatPublishes as $eduMat) {
		array_push($eduMatList, array('serial' => $eduMat['serial'], 'publish' => $eduMat['publish']));
	}

	// Call function
  $response = $eduMatObject->updatePublishFlags($eduMatList, $user);
  print json_encode($response); // Return response
?>
