<?php
include_once("../config.php");

$alias = new Alias();
$result = $alias->getAliasDetails($_POST);

header('Content-Type: application/javascript');
echo json_encode($result);