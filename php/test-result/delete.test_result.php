<?php
include_once("../config.php");
$testResult = new TestResult;

$serial = $_POST['serial'];
$user = $_POST['user'];

$response = $testResult->deleteTestResult($serial, $user);
header('Content-Type: application/javascript');
echo json_encode($response);