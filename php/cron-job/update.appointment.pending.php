<?php
include_once("../config.php");

$cron = new CronJob(); // Object
$cron->updateAppointmentPending();

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);