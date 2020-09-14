<?php

include_once("../config.php");
   
$trigger = new Trigger();
$sourceModuleId = MODULE_QUESTIONNAIRE; // define what type of trigger this is

// Need patientQuestionnaireSerNum from caller
$trigger->executeTrigger($_POST, $sourceModuleId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);

?>
