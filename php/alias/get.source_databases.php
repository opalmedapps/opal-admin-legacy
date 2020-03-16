<?php
header('Content-Type: application/javascript');

include_once('alias.inc');

$type = $_POST['type'];
$alias = new Alias; // Object
$sourceDBList = $alias->getSourceDatabases();

echo json_encode($sourceDBList);