<?php

include_once('user.inc');

$id = strip_tags($_POST["ID"]);

$user = new User();
$user->deleteUser($id);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);