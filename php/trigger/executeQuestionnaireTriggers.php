<?php

include_once("../config.php");

$trigger = new Trigger(true); // guest status on for now
$validatedPost = $trigger->_validateTrigger($_POST); // TO FIX: call to private function 

$patientQuestionnaireSer = $validatedPost["id"];
$patientId = $validatedPost['patientId'];
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
$questionnaireId = $questionnaireData["questionnaire_id"];
$patientId = $questionnaireData["patient_id"];
$answers = $questionnaireData["answers"];

$triggers = $trigger->getTriggers($questionnaireId, $triggerType);

foreach ($triggers as $index => $details) {
    if($trigger->checkLogic($details, $questionnaireData)) {
        $trigger->triggerEvent($details, $patientId);
    }
}

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);

?>