<?php

header('Content-Type: application/javascript');
include_once("../config.php");

// Construct array from FORM params
$testResult = new TestResult; // Object

$response = $testResult->insertTestResult($_POST);
print json_encode($response); // Return response

