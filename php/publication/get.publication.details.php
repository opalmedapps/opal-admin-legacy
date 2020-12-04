<?php
include_once('publication.inc');

$publicationId = strip_tags($_POST['publicationId']);
$moduleId = strip_tags($_POST['moduleId']);

$publication = new Publication();
$publicationDetails = $publication->getPublicationDetails($publicationId, $moduleId);

header('Content-Type: application/javascript');
echo json_encode($publicationDetails);