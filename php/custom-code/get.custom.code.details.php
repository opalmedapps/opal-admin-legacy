<?php
include_once('custom.code.inc');

$customCodeId = strip_tags($_POST['customCodeId']);
$moduleId = strip_tags($_POST['moduleId']);

$customCode = new CustomCode(); // Object
$response = $customCode->getCustomCodeDetailsAPI($customCodeId, $moduleId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response