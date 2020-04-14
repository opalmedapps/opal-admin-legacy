<?php

include_once('user.inc');

$username = strip_tags($_POST['username']);
$userObj = new User(); // Object
$count = $userObj->usernameAlreadyInUse($username);

header('Content-Type: application/javascript');
echo json_encode(array("count"=>$count));