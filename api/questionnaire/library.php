<?php
// get libraries
include_once('questionnaire.inc');

$callback = $_GET['callback'];
$userid = $_GET['userid'];

$library = new Library();

$libraryList = $library->getLibrary($userid);

// Callback to http request
print $callback.'('.json_encode($libraryList).')';
?>