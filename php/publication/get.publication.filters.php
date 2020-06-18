<?php

include_once('publication.inc');

$filter = new Publication();
$result = $filter->getFilters();

header('Content-Type: application/javascript');
echo json_encode($result);