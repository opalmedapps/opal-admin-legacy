<?php

include_once("../config.php");

$trigger = new Trigger(true); // guest status on for now
$validatedPost = $trigger->_validateTrigger($_POST);

$patientQuestionnaireSer = $validatedPost["id"];
$triggerType = MODULE_QUESTIONNAIRE; 


/*
-- Receive patientQuestionnaireSerial
-- Get patient serial & questionnaire data using stored procedure
-- Query database for all logic for this questionnaire using questionnaire serial
-- Foreach logic, test rule
-- If passes, publish/insert secondary questionnaire using patient serial
*/

$questionnaireData = $trigger->getData($patientQuestionnaireSer, $triggerType); 

// Need questionnaire id + patient serial from questionnaireData (Results #1)
$questionnaireId = '';
$patientSer = '';

/*
Need that answer_summary results
*/
$answers = [];
foreach ($questionnaireData->{'Data'}->{'sections'} as $sectionIndex => $section) {
	foreach ($section->{'questions'} as $questionIndex => $question) {
		foreach ($question->{'patient_answer'}->{'answer'} as $answerIndex => $answer) {
			// add questionnaire id to answer object
			$answer->{'questionnaire_id'} = $questionnaireData->{'Data'}->{'questionnaire_id'};
			array_push($answers, $answer); 
		}
	}
}


$triggers = $trigger->getTriggers($questionnaireId, $triggerType);

foreach ($triggers as $index => $details) {
    if($trigger->checkLogic($details, $answers)) {
        $trigger->triggerEvent($details, $patientSer);
    }
}

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);

echo json_encode($results);

?>