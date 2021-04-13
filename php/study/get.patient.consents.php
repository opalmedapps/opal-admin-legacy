<?php
include_once("../config.php");

$studyId = strip_tags($_POST['studyId']);

$study = new Study(); // Object
$results = $study->getPatientsConsentList($studyId);

header('Content-Type: application/javascript');
echo json_encode($results);