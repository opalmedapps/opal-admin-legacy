<?php

include_once('publication.inc');

$publication = new Publication();
$result = $publication->getPublications();

header('Content-Type: application/javascript');
echo json_encode($result);