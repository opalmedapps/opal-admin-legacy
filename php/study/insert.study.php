<?php
include_once("../config.php");

$customCode = new Study();
$customCode->insertStudy($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);