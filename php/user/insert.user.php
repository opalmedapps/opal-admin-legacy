<?php
include_once('user.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);
$userObj = new User($OAUserId);
$userObj->registerUser($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);