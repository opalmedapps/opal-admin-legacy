<?php

include_once('user.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);
$userObject = new User($OAUserId);

$response["message"] = $userObject->updatePassword($_POST);
$response["code"] = HTTP_STATUS_SUCCESS;

header('Content-Type: application/javascript');
print json_encode($response);