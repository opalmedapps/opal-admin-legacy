<?php
include_once('user.inc');

$userObj = new User();
$userObj->insertUser($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);