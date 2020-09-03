<?php

require('../lib/JWadhams/JsonLogic.php');
/*
 * Trigger class objects and method
 * */

 class Trigger extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_TRIGGER, $guestStatus);
    }

    public function getTriggers($id, $type) { 
        // $this->checkReadAccess();

        return $this->opalDB->getTriggersList($id, $type);
    }


    protected function _validateTrigger($postData, $triggerType) {
        $validatedTrigger = array();
        $postData = HelpSetup::arraySanitization($postData);

        // Check id
        if($postData["id"] != "")
            $validatedTrigger["id"] = trim(strip_tags($postData["id"]));
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing trigger ID.");

        // Check id
        if($triggerType != "")
            $validatedTrigger["trigger_type"] = $triggerType;
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing trigger type.");

        return $validatedTrigger;

    }

    public function getData($id, $dataType) {
        // $this->checkReadAccess();

        switch ($dataType) {
            case MODULE_QUESTIONNAIRE:
                $questionnaire = new Questionnaire(true);
                return $questionnaire->getQuestionnaireResults($id); /// there's a stored procedure for this
                break;
            
            default:
                return array();
                break;
        }
        
    }

    public function checkLogic($trigger, $dataToCheck) {
        return JWadhams\JsonLogic::apply( json_decode($trigger["onCondition"], true), $dataToCheck );
    }

    public function triggerEvent($triggerDetails, $patientId) {
        switch ($triggerDetails['eventType']) {
            case TRIGGER_EVENT_PUBLISH:
                $this->publish($triggerDetails, $patientId);
                break;
            
            default:
                # code...
                break;
        }
    }

    public function publish($triggerDetails, $patientId) {
        switch ($triggerDetails["targetType"]) {
            case MODULE_QUESTIONNAIRE:
                //$this->opalDB->publishQuestionnaire($triggerDetails["targetId"], $patientId);
                echo "PUBLISH QUESTIONNAIRE!";
                break;
            
            case MODULE_ALERT:
                // Need an alert table for publishing alerts
                break;
            
            case MODULE_EDU_MAT:
                //$this->opalDB->publishEducationalMaterial($triggerDetails["targetId"], $patientId);
                break;

            case MODULE_POST:
                // Need to separate post 
                break;
            
            default:
                # code...
                break;
        }

    }

    public function executeTrigger($postData, $triggerType) {

        $validatedData = $this->_validateTrigger($postData, $triggerType);
        $id = $validatedData["id"];
        $triggerType = $validatedData["trigger_type"];

        $triggerId = "";
        $patientId = ""; 

        $dataToCheck = $this->getData($id, $triggerType); 

        if (!empty($dataToCheck)) {
            switch ($triggerType) {
                case MODULE_QUESTIONNAIRE:
                    $triggerId = $dataToCheck["questionnaire_id"]; // to pull triggers related to this questionnaire
                    $patientId = $dataToCheck["patient_id"]; // which patient to trigger event on
                    break;

                default:
                    break;
            }
        }
        
        // Retrieve all triggers 
        $triggers = $this->getTriggers($triggerId, $triggerType);

        foreach ($triggers as $index => $triggerDetails) {
            if($this->checkLogic($triggerDetails, $dataToCheck)) { // if trigger should be fired
                $this->triggerEvent($triggerDetails, $patientId); 
            }
        }

        return;
    }

 }