<?php
header('Content-Type: application/javascript');
/* To get filters (expression, dx, doctor, resource)*/
include_once('filter.inc');

$filterObject = new Filter; // Object
$filters = $filterObject->getFilters();

echo json_encode($filters);