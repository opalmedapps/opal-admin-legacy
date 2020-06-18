<?php

include_once('custom.code.inc');

$customCode = new CustomCode();
$customCode->updateCustomCode($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);