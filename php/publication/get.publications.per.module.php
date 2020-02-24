<?php
include_once('publication.inc');

$OAUserId = strip_tags($_POST['OAUserId']);
$moduleId = strip_tags($_POST['moduleId']);
$publication = new Publication($OAUserId);
$result = $publication->getPublicationsPerModule($moduleId);


header('Content-Type: application/javascript');
echo json_encode($result);