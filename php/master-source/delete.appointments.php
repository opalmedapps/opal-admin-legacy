<?php

include_once("../config.php");

$sourceDiag = new MasterSourceAppointment(); // Object
$results = $sourceDiag->markAsDeletedSourceAppointments($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);