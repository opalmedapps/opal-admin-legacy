<?php

include_once('publication.inc');

$OAUserId = strip_tags($_POST['OAUserId']);
$publication = new Publication($OAUserId);
$result = $publication->getPublications();

header('Content-Type: application/javascript');
echo json_encode($result);