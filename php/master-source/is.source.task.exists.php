<?php

include_once("../config.php");

$sourceDiag = new MasterSourceTask(); // Object
$results = $sourceDiag->doesTaskExists($_POST);

header('Content-Type: application/javascript');
echo json_encode($results);