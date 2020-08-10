<?php

include_once('user.inc');

$userObject = new User(); // Object
$users = $userObject->getUsers();

header('Content-Type: application/javascript');
echo json_encode($users);