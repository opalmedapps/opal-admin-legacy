<?php

header('Content-Type: application/javascript');
include_once('user.inc');

$userObject = new Users; // Object
$users = $userObject->getUsers();

echo json_encode($users);