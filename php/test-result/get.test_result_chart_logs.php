<?php
include_once("../config.php");

$serial = ( strip_tags($_POST['serial']) === 'undefined' ) ? null : strip_tags($_POST['serial']);
$testResult = new TestResult; // Object
$testResultLogs = $testResult->getTestResultChartLogs($serial);

header('Content-Type: application/javascript');
echo json_encode($testResultLogs);
