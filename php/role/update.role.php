<?php

include_once('role.inc');

$role = new Role();
$role->updateRole($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);