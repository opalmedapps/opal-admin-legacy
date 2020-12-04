<?php

include_once('study.inc');

$customCode = new Study();
$customCode->insertStudy($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);