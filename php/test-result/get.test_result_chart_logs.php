<?php

header('Content-Type: application/javascript');
include_once('test-result.inc');

$serial = ( strip_tags($_POST['serial']) === 'undefined' ) ? null : strip_tags($_POST['serial']);
$testResult = new TestResult; // Object
$testResultLogs = $testResult->getTestResultChartLogs($serial);

echo json_encode($testResultLogs);
