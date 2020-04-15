<?php

include_once('user.inc');

$id = strip_tags($_POST["ID"]);
$OAUserId = strip_tags($_POST["OAUserId"]);

$user = new User($OAUserId);
$user->deleteUser($id);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);