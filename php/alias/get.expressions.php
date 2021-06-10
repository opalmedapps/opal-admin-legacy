<?php
include_once("../config.php");

$sourceDBSer = $_POST['sourcedbser'];
$type = $_POST['type'];

$alias = new Alias();

$expressionList = $alias->getExpressions($sourceDBSer, $type);

header('Content-Type: application/javascript');
echo json_encode($expressionList);