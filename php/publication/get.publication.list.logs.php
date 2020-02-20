<?php

include_once('publication.inc');

$cronIds = json_decode(strip_tags($_POST['cronIds']), true);
$publicationId = strip_tags($_POST['publicationId']);
$moduleId = strip_tags($_POST['moduleId']);
$OAUserId = strip_tags($_POST['OAUserId']);

$publication = new Publication($OAUserId);
$result = $publication->getPublicationListLogs($moduleId, $publicationId, $cronIds);

header('Content-Type: application/javascript');
echo json_encode($result);