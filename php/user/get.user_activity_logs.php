<?php

header('Content-Type: application/javascript');
include_once('user.inc');

$userSer    = strip_tags($_POST['userser']);
$userObject = new Users; // Object
$userLogs = $userObject->getUserActivityLogs($userSer);

echo json_encode($userLogs);