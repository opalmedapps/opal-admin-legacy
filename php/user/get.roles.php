<?php

header('Content-Type: application/javascript');
include_once('user.inc');

$userObject = new Users; // Object
$roles = $userObject->getRoles();

echo json_encode($roles);