<?php
include_once("../config.php");
header('Content-Type: application/javascript');

$testResult = new TestResult; // Object

// Construct array from FORM params
$response = $testResult->updateTestResult($_POST);

print json_encode($response); // Return response
