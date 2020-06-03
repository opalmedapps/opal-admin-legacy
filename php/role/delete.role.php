<?php

include_once('role.inc');

$roleId = strip_tags($_POST['roleId']);
$OAUserId = strip_tags($_POST['OAUserId']);

$role = new Role($OAUserId); // Object
$response = $role->deleteRole($roleId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response