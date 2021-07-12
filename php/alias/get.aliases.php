<?php
include_once("../config.php");

$alias = new Alias();
$result = $alias->getAliases();

header('Content-Type: application/javascript');
echo json_encode($result);