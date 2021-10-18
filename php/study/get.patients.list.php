<?php
include_once("../config.php");

$study = new Study(); // Object
$results = $study->getPatientsList();

header('Content-Type: application/javascript');
echo json_encode($results);