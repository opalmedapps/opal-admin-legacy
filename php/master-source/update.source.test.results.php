<?php

include_once("../config.php");

$sourceDiag = new MasterSourceTestResult(); // Object
$results = $sourceDiag->updateSourceTestResults($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);