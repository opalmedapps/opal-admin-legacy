<?php

include_once("../config.php");

/*
-- Receive patientQuestionnaireSerial
-- Get patient id & questionnaire data using stored procedure
-- Query database for all logic for this questionnaire 
-- Foreach logic, test rule
-- If passes, publish/insert secondary questionnaire using patient serial
*/

$trigger = new Trigger(true); // guest status on for now

$triggerType = MODULE_QUESTIONNAIRE; // define what type of trigger this is

$questionnaireData = $trigger->getData($_POST, $triggerType); 

$questionnaireId = $questionnaireData["questionnaire_id"]; // to pull triggers related to this questionnaire
$patientId = $questionnaireData["patient_id"]; // which patient to trigger event on
$answers = $questionnaireData["answers"]; // relevant questionnaire results

// Retrieve all triggers for this questionnaire
$triggers = $trigger->getTriggers($questionnaireId, $triggerType);

foreach ($triggers as $index => $triggerDetails) {
    if($trigger->checkLogic($triggerDetails, $questionnaireData)) { // if trigger should be fired
        $trigger->triggerEvent($triggerDetails, $patientId); 
    }
}

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);

?>