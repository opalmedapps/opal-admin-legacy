<?php
include_once("../config.php");

$cron = new CronJob(); // Object
$cron->updateAppointmentCheckIn($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);