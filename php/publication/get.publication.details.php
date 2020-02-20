<?php
include_once('publication.inc');

$publicationId = strip_tags($_POST['publicationId']);
$moduleId = strip_tags($_POST['moduleId']);
$OAUserId = strip_tags($_POST['OAUserId']);

$publication = new Publication($OAUserId);
$publicationDetails = $publication->getPublicationDetails($publicationId, $moduleId);

header('Content-Type: application/javascript');
echo json_encode($publicationDetails);