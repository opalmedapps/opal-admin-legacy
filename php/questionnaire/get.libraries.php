<?php
/**
 * User: Dominic Bourdua
 * Date: 4/16/2019
 * Time: 1:45 PM
 */

include_once('questionnaire.inc');

$questionLibrary = new Library();
$result = $questionLibrary->getLibraries();

header('Content-Type: application/javascript');
echo json_encode($result);