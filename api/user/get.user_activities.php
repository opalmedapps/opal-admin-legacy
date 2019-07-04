<?php

header('Content-Type: application/javascript');
include_once('user.inc');

$user = new Users; // Object
$userActivityList = $user->getUserActivities();
echo json_encode($userActivityList);