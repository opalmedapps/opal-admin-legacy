<?php

include_once('study.inc');

$OAUserId = strip_tags(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $_POST['OAUserId']));


$testPost = array(
    "OAUserId"=>20,
    "details"=>array(
        "code"=>"test",
        "title"=>"This is the title"
    ),
    "investigator"=>array(
        "name"=>"Gregory House"
    ),
    "dates"=>array(
        "start_date"=>"Gregory 1590465600",
        "end_date"=>""
    ),
);




print_r($testPost);
print_r($_POST);die();

$customCode = new Study($OAUserId);
//$customCode->insertStudy($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);