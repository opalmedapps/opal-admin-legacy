<?php
include_once("../config.php");

$testResult = new TestResult(); // Object
$existingTestResultList = $testResult->getTestResults();

include_once('test-result.inc');
echo json_encode($existingTestResultList);