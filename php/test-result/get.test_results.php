<?php
include_once("../config.php");

$testResult = new TestResult(); // Object
$existingTestResultList = $testResult->getTestResults();

echo json_encode($existingTestResultList);