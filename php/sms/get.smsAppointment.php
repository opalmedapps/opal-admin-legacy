<?php

include_once('sms.inc');

$sms = new Sms();
$smsAppointmentList = $sms->getAppointments();

echo json_encode($smsAppointmentList);
