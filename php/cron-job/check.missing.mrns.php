<?php
include_once("../config.php");

$cron = new CronJob(); // Object
$cron->findPatientsWithMissingMRNs();

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);