<?php

include_once('user.inc');

$userObject = new User();
$userDetails = $userObject->getUserDetails($_POST);

header('Content-Type: application/javascript');
echo json_encode($userDetails);