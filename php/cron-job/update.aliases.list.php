<?php

include_once('cron-job.inc');

$publication = new CronJob(OAUSER_CRONJOB);
$result = $publication->updateAliasesList();

header('Content-Type: application/javascript');
die("all good");
echo json_encode($result);