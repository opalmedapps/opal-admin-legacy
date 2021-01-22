<?php
include_once("../config.php");

$testResult = new TestResult();
$testResultDetails = $testResult->getTestResultDetails($_POST);

header('Content-Type: application/javascript');
echo json_encode($testResultDetails);