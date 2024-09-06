<?php
header('Content-Type: application/javascript');
/* To get cron logs for charts */
include_once('cron.inc');

$cron = new Cron; // Object

