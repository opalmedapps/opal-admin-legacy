<?php
include_once("../config.php");

$userObject = new User();
$userDetails = $userObject->getUserDetails($_POST);

header('Content-Type: application/javascript');
echo json_encode($userDetails);