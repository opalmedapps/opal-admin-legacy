<?php
/**
 * User: Dominic Bourdua
 * Date: 4/16/2019
 * Time: 1:45 PM
 */

/* To get a list of existing question groups */
include_once('questionnaire.inc');

// Retrieve form params
$callback = strip_tags($_GET['callback']);
$userId = strip_tags($_GET['userid']);

$questionLibrary = new Library($userId); // Object

// Call function
$result = $questionLibrary->getLibraries();

// Callback to http request
header('Content-Type: application/javascript');
print $callback.'('.json_encode($result).')';
?>
