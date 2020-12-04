<?php

include_once('publication.inc');

$publication = new Publication();
$result = $publication->getPublicationModulesUser();

header('Content-Type: application/javascript');
echo json_encode($result);