<?php
include_once('custom.code.inc');

$customCodeId = strip_tags($_POST['customCodeId']);
$moduleId = strip_tags($_POST['moduleId']);
$OAUserId = strip_tags($_POST['OAUserId']);

$customCode = new CustomCode($OAUserId); // Object
$response = $customCode->getCustomCodeDetails($customCodeId, $moduleId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response