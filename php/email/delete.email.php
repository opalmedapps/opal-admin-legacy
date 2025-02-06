<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
	/* To delete an email */
	include_once('email.inc');

	$email = new Email; // Object

	// Retrieve FORM param
	$serial	= $_POST['serial'];
	$user		= $_POST['user'];

	// Call function
	$response = $email->deleteEmail($serial, $user);

	print json_encode($response); // Return response

?>
