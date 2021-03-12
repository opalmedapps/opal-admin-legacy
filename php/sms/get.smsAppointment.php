<?php

include_once('sms');

$sms = new Sms();
$sms->getAppointments();

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
