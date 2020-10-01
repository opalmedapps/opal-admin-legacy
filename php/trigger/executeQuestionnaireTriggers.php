<?php

include_once("../config.php");
   
$trigger = new Trigger(true);
$sourceModuleId = MODULE_QUESTIONNAIRE; // define what type of trigger this is

// Need patientQuestionnaireSerNum from caller
$result = $trigger->executeTrigger($_POST, $sourceModuleId);
//$result = $trigger->executeTrigger(array("id" => 200), $sourceModuleId);

header('Content-Type: application/javascript');
echo json_encode($result); // Return response

?>
