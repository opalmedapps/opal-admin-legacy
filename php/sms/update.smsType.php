<?php

header('Content-Type: application/javascript');
include_once('sms.inc');

$sms = new Sms();

$information = $_POST['information'];

$response = $sms->updateAppointmentType($information);

echo json_encode($response);
