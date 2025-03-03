<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

// Construct array from FORM params
$aliasArray	= array(
	'name_EN' 	        => $_POST['name_EN'],
	'name_FR' 	        => $_POST['name_FR'],
	'description_EN'    => filter_var($_POST['description_EN'], FILTER_SANITIZE_ADD_SLASHES),
	'description_FR'    => filter_var($_POST['description_FR'], FILTER_SANITIZE_ADD_SLASHES),
	'serial' 	        	=> $_POST['serial'],
	'type' 		        	=> $_POST['type'],
	'color'             => $_POST['color'],
	'edumat'            => $_POST['eduMat'],
	'source_db'         => $_POST['source_db'],
	'terms' 	       	 	=> $_POST['terms'],
	'user'							=> $_POST['user'],
	'checkin_details'		=> $_POST['checkin_details'],
	'hospitalMap'				=> $_POST['hospitalMap']
);

$aliasObject = new Alias(); // Object

// Call function
print $aliasObject->insertAlias($_POST);
