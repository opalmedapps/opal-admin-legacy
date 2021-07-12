<?php
header('Content-Type: application/javascript');
/* To get details on a cron profile */
include_once('cron.inc');

$cron = new Cron; // Object
$cronDetails = $cron->getCronDetails();

// Callback to http request
echo json_encode($cronDetails);