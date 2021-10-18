<?php
include_once('alias.inc');
header('Content-Type: application/javascript');

$alias = new Alias; // Object
$existingAliasList = $alias->getAliases();

echo json_encode($existingAliasList);