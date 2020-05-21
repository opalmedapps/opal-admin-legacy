<?php

include_once('role.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);

$rolew = new Role($OAUserId); // Object
$results = $rolew->getRoles();

header('Content-Type: application/javascript');
echo json_encode($results);