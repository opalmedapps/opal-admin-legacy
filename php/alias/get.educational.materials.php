<?php
include_once('alias.inc');

$alias = new Alias; // Object
$results = $alias->getEducationalMaterials();

header('Content-Type: application/javascript');
echo json_encode($results);