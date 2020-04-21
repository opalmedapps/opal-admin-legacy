<?php

/* Simple logout script */
include_once('user.inc');

// Retrieve post data
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

$OAUserId = strip_tags($_POST["id"]);
$userObject = new User($OAUserId);
$response = $userObject->userLogout($_POST);

header('Content-Type: application/javascript');
echo json_encode($response);