<?php

include_once("../config.php");

$sourceDiag = new MasterSourceTestResult(); // Object
$results = $sourceDiag->doesTestResultExists($_POST);

header('Content-Type: application/javascript');
echo json_encode($results);