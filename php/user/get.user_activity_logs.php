<?php

include_once("../config.php");
$userSer    = strip_tags($_POST['userser']);
$userObject = new User(); // Object
$userLogs = $userObject->getUserActivityLogs($userSer);

header('Content-Type: application/javascript');
echo json_encode($userLogs);