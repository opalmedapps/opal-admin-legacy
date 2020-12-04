<?php
include_once("../config.php");

$diagnosis = new Diagnosis();

$response = $diagnosis->updateDiagnosisTranslation($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);