<?php

include_once("../config.php");

$sourceDiag = new MasterSourceAlias(); // Object
$results = $sourceDiag->doesAliasExists($_POST);

header('Content-Type: application/javascript');
echo json_encode($results);