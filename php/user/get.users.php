<?php

header('Content-Type: application/javascript');
include_once('user.inc');

$userObject = new User(); // Object
$users = $userObject->getUsers();

echo json_encode($users);