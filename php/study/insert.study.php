<?php

include_once('study.inc');

$customCode = new Study();
$customCode->insertStudy($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);