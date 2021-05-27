<?php

include_once("../config.php");

$alias = new Alias(); // Object
$aliasLogs = $alias->getAliasChartLogs($_POST);

header('Content-Type: application/javascript');
echo json_encode($aliasLogs);