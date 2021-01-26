<?php
include_once("../config.php");

$testResult = new TestResult(); // Object
$testResultLogs = $testResult->getTestResultChartLogs($_POST);

header('Content-Type: application/javascript');
echo json_encode($testResultLogs);
