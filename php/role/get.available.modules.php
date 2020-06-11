<?php

include_once('role.inc');

$role = new Role(); // Object
$results = $role->getAvailableModules();

header('Content-Type: application/javascript');
echo json_encode($results);