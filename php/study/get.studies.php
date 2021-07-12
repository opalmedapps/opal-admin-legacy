<?php
include_once("../config.php");

$study = new Study(); // Object
$results = $study->getStudies();

header('Content-Type: application/javascript');
echo json_encode($results);