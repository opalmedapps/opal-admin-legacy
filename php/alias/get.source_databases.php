<?php
include_once("../config.php");

$alias = new Alias();
$sourceDBList = $alias->getSourceDatabases();

header('Content-Type: application/javascript');
echo json_encode($sourceDBList);