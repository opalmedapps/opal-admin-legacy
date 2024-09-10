<?php
header('Content-Type: application/javascript');
/* To get cron logs for charts */
include_once('cron.inc');

// Retrieve FORM params
$contents = json_decode($_POST['contents'], true);

$cron = new Cron; // Object
