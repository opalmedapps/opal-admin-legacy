<?php
header('Content-Type: application/javascript');
/* To get a list of expressions from a particular source database*/
include_once('alias.inc');

$sourceDBSer = $_POST['sourcedbser'];
$type = $_POST['type'];

$alias = new Alias; // Object

// Call function
$expressionList = $alias->getExpressions($sourceDBSer, $type);

// Callback to http request
echo json_encode($expressionList);