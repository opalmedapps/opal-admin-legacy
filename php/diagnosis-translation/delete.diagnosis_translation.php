<?php
include_once("../config.php");

$serial = $_POST['serial'];
$user = $_POST['user'];

$diagnosis = new Diagnosis();
$response = $diagnosis->deleteDiagnosisTranslation($serial, $user);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
