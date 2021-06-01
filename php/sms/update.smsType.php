<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms();

$information = strip_tags($_POST['information']);

$response = $sms->updateAppointmentType($information);

echo json_encode($response);
