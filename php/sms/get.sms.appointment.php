<?php
include_once("../config.php");

$sms = new Sms();
$smsAppointmentList = $sms->getAppointments();

header('Content-Type: application/javascript');
echo json_encode($smsAppointmentList);
