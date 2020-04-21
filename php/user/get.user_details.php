<?php

include_once('user.inc');

$OAUserId = strip_tags($_POST['OAUserId']);
$userObject = new User($OAUserId);
$userDetails = $userObject->getUserDetails($_POST);

header('Content-Type: application/javascript');
echo json_encode($userDetails);