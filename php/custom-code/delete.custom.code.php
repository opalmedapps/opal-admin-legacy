<?php

/* To delete a custom code */
include_once('custom.code.inc');

$customCodeId = strip_tags($_POST['customCodeId']);
$moduleId = strip_tags($_POST['moduleId']);

$customCode = new CustomCode(); // Object
$response = $customCode->deleteCustomCode($customCodeId, $moduleId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);