<?php
include_once("../config.php");

$serial = strip_tags($_POST['serial']);
$patientObj = new Patient(); // Object
$patientDetails = $patientObj->getPatientDetails($serial);

header('Content-Type: application/javascript');
echo json_encode($patientDetails);