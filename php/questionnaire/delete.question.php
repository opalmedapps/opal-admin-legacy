<?php
include_once("../config.php");

// Retrieve FORM param
$serNum = strip_tags($_POST['ID']);

// Call function
$questionObj = new Question(); // Object
$response = $questionObj->deleteQuestion($serNum);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);