<?php

include_once('user.inc');

$userObject = new User();

$userObject->updateUser($_POST);
header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;