<?php
header('Content-Type: application/javascript');
include_once('alias.inc');

$alias = new Alias; // Object
$results = $alias->getEducationalMaterials();

echo json_encode($results);