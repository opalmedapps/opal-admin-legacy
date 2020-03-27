<?php

header('Content-Type: application/javascript');
include_once('user.inc');

$userSer    = $_POST['userser'];
$userObject = new Users; // Object
$userDetails = $userObject->getUserDetails($userSer);

echo json_encode($userDetails);