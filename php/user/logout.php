<?php

/* Simple logout script */
include_once('user.inc');

$userObject = new User();
$response = $userObject->userLogout();

header('Content-Type: application/javascript');
echo json_encode($response);