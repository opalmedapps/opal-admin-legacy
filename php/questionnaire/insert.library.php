<?php
/* To insert a newly-created library */
include_once('questionnaire.inc');

// Construct array from FORM params
$libraryArray = array(
    'name_EN' => strip_tags($_POST['name_EN']),
    'name_FR' => strip_tags($_POST['name_FR']),
    'private' => strip_tags($_POST['private'])
);

$libraryObj = new Library(); // Object

// Call function
$libraryObj->insertLibrary($libraryArray);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);