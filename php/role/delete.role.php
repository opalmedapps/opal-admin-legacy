<?php

include_once('role.inc');

$roleId = strip_tags($_POST['roleId']);

$role = new Role(); // Object
$response = $role->deleteRole($roleId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);