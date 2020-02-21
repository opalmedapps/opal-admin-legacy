<?php
header('Content-Type: application/javascript');
/* To get details on a particular alias */
include_once('alias.inc');

// Retrieve FORM params
$serial = strip_tags($_POST['serial']);
$alias = new Alias; // Object
$AliasDetails = $alias->getAliasDetails($serial);

// Callback to http request
echo json_encode($AliasDetails);