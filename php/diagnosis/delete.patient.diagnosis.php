<?php

include_once("../config.php");

$diagnosis = new Diagnosis();

$diagnosis->deletePatientDiagnosis($_POST);
header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);