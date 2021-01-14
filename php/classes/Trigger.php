<?php

require('../lib/JWadhams/JsonLogic.php');
/*
 * Trigger class object and methods
 * */

class Trigger extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_TRIGGER, $guestStatus);
    }

    /*
     * Validate and sanitize post data and check if module id is set. If there is a problem return an error 500.
     * @params  array  $postdata : POST data containing a starting-point identifier
     * @params  integer  $sourceModuleId : the module id of the source content 
     * @return int $errCode : error code (if any error found. 0 = no error)
     * */
    protected function _validateTrigger(&$postData, &$moduleId) {
        $postData = HelpSetup::arraySanitization($postData);
        $errCode = "";

        // Check id
        if(strip_tags($postData["id"]) != "") {
            $postData["id"] = strip_tags($postData["id"]);
            $errCode = "0" . $errCode;
        }
        else
            $errCode = "1" . $errCode;

        // Check module id
        if(in_array(strip_tags($moduleId), MODULE_PUBLICATION_TRIGGER)) {
            $errCode = "0" . $errCode;
            $moduleId = strip_tags($moduleId);
        }
        else
            $errCode = "1" . $errCode;

        if(strip_tags($postData["language"]) == "")
            $postData["language"] = ABVR_FRENCH_LANGUAGE;
        else
            $postData["language"] = strip_tags($postData["language"]);

        return bindec($errCode);
    }

    /*
     * Function that gets the necessary data that will be used to check the logic against
     * @params  integer  $id : starting-point identifier to search for source data
     * @params  integer  $sourceModuleId : the module id of the source content
     * @return  array : respective data based on module 
     * */
    protected function _getData($id, $sourceModuleId, $language) {
        $result = array();
        switch ($sourceModuleId) {
            case MODULE_QUESTIONNAIRE:
                $questionnaire = new Questionnaire(true);
                $result = $questionnaire->getQuestionnaireResults($id, $language); /// ultimately there's a stored procedure for this
                break;
        }
        return $result;

    }

    /*
     * Execute event method when trigger logic passes 
     * @params  array  $trigger : current trigger entry
     * @params  integer  $patientSerNum : patient serial 
     * @return  
     * */
    protected function _triggerEvent(&$trigger, &$patientSerNum) {
        $result = false;
        switch ($trigger['eventType']) {
            case TRIGGER_EVENT_PUBLISH: // only one for now
                $result = $this->_publish($trigger, $patientSerNum);
                break;
        }
        return $result;
    }

    /*
     * An event type method for publishing (i.e. inserting) content
     * @params  array  $trigger : current trigger entry
     * @params  integer  $patientSerNum : patient serial
     * @return  
     * */
    protected function _publish(&$trigger, &$patientSerNum) {
        $result = false;

        switch ($trigger["targetModuleId"]) {
            case MODULE_QUESTIONNAIRE:
                $result = $this->opalDB->publishQuestionnaire($trigger["targetContentId"], $patientSerNum);
                break;
            case MODULE_ALERT:
                // Need an alert table for publishing alerts
                break;
            case MODULE_EDU_MAT:
                //$this->opalDB->publishEducationalMaterial($trigger["targetContentId"], $patientSerNum); // Not done
                break;
            case MODULE_POST:
                // Need to separate post
                break;
        }
        return $result;
    }

    /*
     * Main method to process triggers
     * @params  array  $postdata : POST data containing a starting-point identifier
     * @params  integer  $sourceModuleId : the module id of the source content 
     * @return  boolean : true if end of method is reached 
     * */
    public function executeTrigger($postData, $sourceModuleId) {

        $eventTriggers = array();

        $errCode = $this->_validateTrigger($postData, $sourceModuleId);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, json_encode(array("validation"=>$errCode)));


        $id = $postData["id"];

        $sourceContentId = "";
        $patientSerNum = "";

        $dataToCheck = $this->_getData($id, $sourceModuleId, $postData["language"]); //get relevant questionnaire id based on patientQuestionnaireSerNum

        if (!empty($dataToCheck)) {
            switch ($sourceModuleId) {
                case MODULE_QUESTIONNAIRE:
                    $sourceContentId = $dataToCheck["questionnaire_id"]; // to pull triggers related to this questionnaire
                    $patientSerNum = $dataToCheck["patient_ser"]; // which patient to trigger event on
                    break;
            }
        }

        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, json_encode(array("validation"=>4)));


        // Retrieve all triggers
        $triggers = $this->opalDB->getTriggersList($sourceContentId, $sourceModuleId); //any relevant triggers for this questionnaire
        //return "all good\n";
        foreach ($triggers as $index => $trigger) {
            if(JWadhams\JsonLogic::apply( json_decode($trigger["onCondition"], true), $dataToCheck )) { // if trigger should be fired
                //return "all good\n\n";
                $eventResponse = $this->_triggerEvent($trigger, $patientSerNum);
                return $eventResponse;
                if ($eventResponse)
                    array_push($eventTriggers, $trigger);
            }
        }

        return $eventTriggers;
    }
}
