<?php
include_once("../config.php");

$patient = new Patient();

$data = json_decode(file_get_contents('php://input'), true);
print_r($data);
$patient->updatePatient($data);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);