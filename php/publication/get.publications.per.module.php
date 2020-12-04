<?php
include_once('publication.inc');

$moduleId = strip_tags($_POST['moduleId']);
$publication = new Publication();
$result = $publication->getPublicationsPerModule($moduleId);


header('Content-Type: application/javascript');
echo json_encode($result);