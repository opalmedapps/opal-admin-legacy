<?php

include_once('filter.inc');

$filter = new Filter();
$result = $filter->getFilters();

header('Content-Type: application/javascript');
echo json_encode($result);