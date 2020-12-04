<?php

include_once('custom.code.inc');

$customCode = new CustomCode();
$customCode->insertCustomCode($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);