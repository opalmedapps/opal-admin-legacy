<?php

include_once("../config.php");
   
$trigger = new Trigger(true);

// Need patientQuestionnaireSerNum from caller
$result = $trigger->executeTrigger($_POST, MODULE_QUESTIONNAIRE);

header('Content-Type: application/javascript');
echo json_encode($result);