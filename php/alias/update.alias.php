<?php
include_once("../config.php");

$aliasObject = new Alias();

$response = $aliasObject->updateAlias($_POST);
header('Content-Type: application/javascript');
echo json_encode($response);