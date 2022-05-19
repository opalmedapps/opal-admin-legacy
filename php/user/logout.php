<?php

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
	header('Location: /opalAdmin/');
	$responseCode = HTTP_STATUS_FOUND;
}

header('Content-Type: application/javascript');
http_response_code($responseCode); //HTTP_STATUS_SUCCESS
