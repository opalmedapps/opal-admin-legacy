<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$userObject = new User();
$response = $userObject->userLogout();

$responseCode = HTTP_STATUS_SUCCESS;

/*
    Set redirection headers if the logout called using GET method.
    This needed for the new opalAdmin. When a user logouts from the new system,
    they will be redirected to the legacy opalAdmin login page.
*/
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	header('Location: /');
	$responseCode = HTTP_STATUS_FOUND;
}

header('Content-Type: application/javascript');
http_response_code($responseCode); //HTTP_STATUS_SUCCESS
