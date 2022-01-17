<?php
include_once("../config.php");

$studyId = strip_tags($_POST['studyId']);

$customCode = new Study(); // Object
$response = $customCode->getStudyDetails($studyId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response