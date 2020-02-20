<?php
header('Content-Type: application/javascript');
/* To get cron logs for highcharts */
include_once('cron.inc');

// Retrieve FORM params
$contents = json_decode($_POST['contents'], true);
$OAUserId = strip_tags($_POST['OAUserId']);

$cron = new Cron; // Object
$cronLogs = $cron->getCronListLogs($contents, $OAUserId);

echo json_encode($cronLogs);