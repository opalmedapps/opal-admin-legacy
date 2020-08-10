<?php
include_once('user.inc');

$userObject = new User();  // Object
$response = $userObject->updateLanguage($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
echo json_encode($response);