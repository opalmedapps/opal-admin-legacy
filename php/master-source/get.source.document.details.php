<?php
include_once("../config.php");

$sourceDiag = new MasterSourceDocument(); // Object
$results = $sourceDiag->getSourceDocumentDetails($_POST);

header('Content-Type: application/javascript');
echo json_encode($results);