<?php

include_once('filter.inc');

$OAUserId = strip_tags($_POST['OAUserId']);

$filter = new Filter($OAUserId);
$result = $filter->getFilters();

header('Content-Type: application/javascript');
echo json_encode($result);