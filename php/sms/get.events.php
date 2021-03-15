<?php

include_once('sms.inc');
$type = strip_tags($_POST['type']);
$speciality = strip_tags($_POST['speciality']);

$sms = new Sms();
$sms->getEvents();

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
