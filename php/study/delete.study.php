<?php

include_once('study.inc');

$studyId = strip_tags($_POST['studyId']);

$study = new Study(); // Object
$response = $study->deleteStudy($studyId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response