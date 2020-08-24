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


    protected function _validateTrigger($postData) {
        $validatedTrigger = array();
        $postData = HelpSetup::arraySanitization($postData);

        // Check id
        if($postData["id"] != "")
            $validatedTrigger["id"] = trim(strip_tags($postData["id"]));
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing trigger ID.");

        if($postData["patientSer"] != "")
            $validatedTrigger["patientSer"] = trim(strip_tags($postData["patientSer"]));
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing patient ID.");

        return $validatedTrigger;

    }

    public function getData($id, $type) {
        // $this->checkReadAccess();

        switch ($type) {
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

    public function triggerEvent($triggerDetails, $patientSer) {
        switch ($triggerDetails['eventType']) {
            case TRIGGER_EVENT_PUBLISH:
                $this->publish($triggerDetails, $patientSer);
                break;
            
            default:
                # code...
                break;
        }
    }

    public function publish($triggerDetails, $patientSer) {
        switch ($triggerDetails["targetType"]) {
            case MODULE_QUESTIONNAIRE:
                $this->opalDB->publishQuestionnaire($triggerDetails["targetId"], $patientSer);
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

 }