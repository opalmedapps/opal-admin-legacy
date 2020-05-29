<?php
include_once('role.inc');

$OAUserId = strip_tags($_POST['OAUserId']);
$roleId = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', strip_tags($_POST['roleId']));

$role = new Role($OAUserId); // Object
$response = $role->getRoleDetails($roleId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response