<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms(); // Object
$smsAppointmentList = $sms->getAppointments();

echo json_encode($smsAppointmentList);
