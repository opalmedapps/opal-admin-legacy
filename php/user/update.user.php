<?php

include_once('user.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);
$userObject = new User($OAUserId);

$userObject->updateUser($_POST);
header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;