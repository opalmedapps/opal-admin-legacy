<?php

include_once('role.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);
$role = new Role($OAUserId); // Object
$results = $role->getAvailableModules();

header('Content-Type: application/javascript');
echo json_encode($results);