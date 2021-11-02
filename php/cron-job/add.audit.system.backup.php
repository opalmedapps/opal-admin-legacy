<?php

include_once("../config.php");

$cron = new CronJob(); // Object
$result = $cron->backupAuditSystem();

header('Content-Type: application/javascript');
echo json_encode($result);