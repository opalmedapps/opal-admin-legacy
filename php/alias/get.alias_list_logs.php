<?php
include_once("../config.php");

$serials = json_decode($_POST['serials']);
$type = ( $_POST['type'] === 'undefined' ) ? null : $_POST['type'];

$alias = new Alias();
$aliasLogs = $alias->getAliasListLogs($_POST);

header('Content-Type: application/javascript');
echo json_encode($aliasLogs);