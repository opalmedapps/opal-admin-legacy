<?php

header('Content-Type: application/javascript');
include_once('test-result.inc');

$testResult = new TestResult; // Object
$existingTestResultList = $testResult->getExistingTestResults();

echo json_encode($existingTestResultList);