<?php

header('Content-Type: application/javascript');
include_once('test-result.inc');

$serial = strip_tags($_POST['serial']);
$testResult = new TestResult; // Object
$testResultDetails = $testResult->getTestResultDetails($serial);

echo json_encode($testResultDetails);