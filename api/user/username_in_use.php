<?php

header('Content-Type: application/javascript');
include_once('user.inc');

$username = strip_tags($_POST['username']);
$userObj = new Users; // Object
$Response = $userObj->usernameAlreadyInUse($username);

echo json_encode($Response);