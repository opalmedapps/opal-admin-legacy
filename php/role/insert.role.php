<?php

include_once('role.inc');

$OAUserId = strip_tags(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $_POST['OAUserId']));

$role = new Role($OAUserId);
$role->insertRole($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);