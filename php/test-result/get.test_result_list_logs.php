<?php
include_once("../config.php");

$serials = json_decode($_POST['serials']);
$testResult = new TestResult; // Object
$testResultLogs = $testResult->getTestResultListLogs($serials);

header('Content-Type: application/javascript');
echo json_encode($testResultLogs);