<?php
include_once("../config.php");

$testResultObject = new TestResult; // Object
$testNames = $testResultObject->getTestNames();

header('Content-Type: application/javascript');
echo json_encode($testNames);