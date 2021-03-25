<?php

include_once('sms.inc');

$sms = new Sms();
echo "check point\n";
$sms->getAppointments();

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
