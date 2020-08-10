<?php

/* Simple logout script */
include_once('user.inc');

$userObject = new User();
$response = $userObject->userLogout();

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);