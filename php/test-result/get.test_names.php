<?php

header('Content-Type: application/javascript');
include_once('test-result.inc');

$testResultObject = new TestResult; // Object
$testNames = $testResultObject->getTestNames();
echo json_encode($testNames);