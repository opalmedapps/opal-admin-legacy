<?php

include_once('user.inc');

$userObject = new User();

$response["message"] = $userObject->updatePassword($_POST);
$response["code"] = HTTP_STATUS_SUCCESS;

header('Content-Type: application/javascript');
print json_encode($response);