<?php

include_once('publication.inc');

$publicationId = strip_tags($_GET['publicationId']);
$moduleId = strip_tags($_GET['moduleId']);
$OAUserId = strip_tags($_GET['OAUserId']);

$publication = new Publication($OAUserId);
header('Content-Type: application/javascript');
$result = $publication->getPublicationChartLogs($moduleId, $publicationId);

echo json_encode($result);