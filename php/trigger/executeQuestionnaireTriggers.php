<?php

include_once("../config.php");

$trigger = new Trigger(true); // guest status on for now
$triggerType = MODULE_QUESTIONNAIRE; // define what type of trigger this is

$trigger->executeTrigger($_POST, $triggerType);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);

?>