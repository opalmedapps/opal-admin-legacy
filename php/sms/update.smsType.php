<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms();

$response = $sms->updateAppointmentType($_POST);

echo json_encode($response);
