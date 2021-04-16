<?php

include_once('sms.inc');
$speciality = strip_tags($_POST['speciality']);
$type = strip_tags($_POST['type']);
$event = strip_tags($_POST['event']);
$language = strip_tags($_POST['language']);

$sms = new Sms();
$sms->getMessage($speciality,$type,$event,$language);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
