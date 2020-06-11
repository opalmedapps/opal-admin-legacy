<?php

include_once('role.inc');

$role = new Role(); // Object
$results = $role->getRoles();

header('Content-Type: application/javascript');
echo json_encode($results);