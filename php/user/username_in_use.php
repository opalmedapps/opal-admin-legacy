<?php
include_once("../config.php");

$username = strip_tags($_POST['username']);
$userObj = new User(); // Object
$count = $userObj->usernameExists($username);

header('Content-Type: application/javascript');
echo json_encode(array("count"=>$count));