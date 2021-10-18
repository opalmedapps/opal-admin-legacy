<?php

header('Content-Type: application/javascript');
include_once('test-result.inc');

$serials = json_decode($_POST['serials']);
$testResult = new TestResult; // Object
$testResultLogs = $testResult->getTestResultListLogs($serials);

echo json_encode($testResultLogs);