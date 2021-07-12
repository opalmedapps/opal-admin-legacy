<?php
include_once("../config.php");
header('Content-Type: application/javascript');

$testResult = new TestResult;
$testResult->updateTestResult($_POST);

http_response_code(HTTP_STATUS_SUCCESS);
