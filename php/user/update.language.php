<?php
include_once('user.inc');

$userObject = new User();  // Object
$response = $userObject->updateLanguage($_POST);

header('Content-Type: application/json');
echo json_encode($response); // Return response
