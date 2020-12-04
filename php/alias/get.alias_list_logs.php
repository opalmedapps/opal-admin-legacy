<?php
header('Content-Type: application/javascript');
/* To get list logs on a particular alias */
include_once('alias.inc');

// Retrieve FORM params
$serials = json_decode($_POST['serials']);
$type = ( $_POST['type'] === 'undefined' ) ? null : $_POST['type'];

$alias = new Alias; // Object
$aliasLogs = $alias->getAliasListLogs($serials, $type);

// // Callback to http request
echo json_encode($aliasLogs);