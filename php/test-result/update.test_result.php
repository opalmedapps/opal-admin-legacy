<?php
include_once("../config.php");

$testResult = new TestResult; // Object

// Construct array from FORM params
$response = $testResult->updateTestResult($_POST);

header('Content-Type: application/javascript');
print json_encode($response); // Return response
