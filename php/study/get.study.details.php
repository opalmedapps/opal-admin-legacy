<?php
include_once('study.inc');

$studyId = strip_tags($_POST['studyId']);
$OAUserId = strip_tags($_POST['OAUserId']);

$customCode = new Study($OAUserId); // Object
$response = $customCode->getStudyDetails($studyId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response