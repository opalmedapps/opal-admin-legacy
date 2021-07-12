<?php
header('Content-Type: application/javascript');
/* To get cron logs for highcharts */
include_once('cron.inc');

$cron = new Cron; // Object
$cronLogs = $cron->getCronChartLogs();

// Callback to http request
echo json_encode($cronLogs);
