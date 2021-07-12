<?php

include_once('publication.inc');

$publicationId = strip_tags($_POST['publicationId']);
$moduleId = strip_tags($_POST['moduleId']);

$publication = new Publication();
$result = $publication->getPublicationChartLogs($moduleId, $publicationId);

header('Content-Type: application/javascript');
echo json_encode($result);