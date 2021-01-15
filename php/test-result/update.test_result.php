<?php
include_once("../config.php");

$testResult = new TestResult;
$testResult->updateTestResult($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
