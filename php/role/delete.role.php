<?php

include_once('role.inc');

$roleId = strip_tags($_POST['roleId']);

$role = new Role(); // Object
$response = $role->deleteRole($roleId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response