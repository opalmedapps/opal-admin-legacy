<?php
include_once("../config.php");

$sourceTR = new MasterSourceTestResult(); // Object
$results = $sourceTR->getSourceTestResultDetails($_POST);

header('Content-Type: application/javascript');
echo json_encode($results);