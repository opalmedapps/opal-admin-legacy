<?php
/* To insert a newly-created question */
include_once('questionnaire.inc');

// Construct an array from FORM params
$questionArray = array(
    'text_EN' => $_POST['text_EN'],
    'text_FR' => $_POST['text_FR'],
    'questiontype_ID' => $_POST['questiontype_ID'],
    'private' => $_POST['private'],
    "libraries"=>$_POST['libraries'],
);

//$questionArray = array(
//    "questiontype_ID"=>"23",
//    "text_EN"=>"What do you think about Oxygen Not Included?",
//    "text_FR"=>"Que pensez-vous de Oxygen Not Included?",
//);



/*$questionArray = array("libraries"=>array("3","1"),
"private"=>"",
"questiontype_ID"=>"4",
"text_EN"=>"Test libraries",
"text_FR"=>"Test librairies",
"userid"=>"20",
);
$userId = 20;*/

$userId = $_POST['userid'];

$questionObj = new Question($userId);
$questionObj->insertQuestion($questionArray);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);
?>
