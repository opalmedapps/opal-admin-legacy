<?php
include_once('user.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);

$userObject = new User($OAUserId);  // Object
$response = $userObject->updateLanguage($_POST);

header('Content-Type: application/json');
echo json_encode($response); // Return response
