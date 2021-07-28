<?php
include_once("../config.php");

$cron = new CronJob(); // Object
$cron->updateResourcePending();

header('Content-Type: application/javascript');
//echo json_encode($existingPostList);