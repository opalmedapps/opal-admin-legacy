<?php

include_once("../config.php");

$patientObj = new Patient; // Object
$response = $patientObj->checkPatientExist($_POST);
echo json_encode($response);