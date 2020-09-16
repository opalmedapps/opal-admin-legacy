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
     * Return the list of triggers in opalDB.
     * @params  integer  $sourceContentId : the source content identifier to look for info
     * @params  integer  $sourceModuleId : the module id of the source content
     * @return  array - list of triggers 
     * */
    public function getTriggers($sourceContentId, $sourceModuleId) { 
        // $this->checkReadAccess();

        return $this->opalDB->getTriggersList($sourceContentId, $sourceModuleId);
    }

    /*
     * Validate and sanitize post data and check if module id is set. If there is a problem return an error 500.
     * @params  array  $postdata : POST data containing a starting-point identifier
     * @params  integer  $sourceModuleId : the module id of the source content 
     * @return array $validatedTrigger : validated response
     * */
    protected function _validateTrigger($postData, $moduleId) {
        $validatedTrigger = array();
        $postData = HelpSetup::arraySanitization($postData);

        // Check id
        if($postData["id"] != "")
            $validatedTrigger["id"] = trim(strip_tags($postData["id"]));
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing trigger ID.");

        // Check id
        if($moduleId != "")
            $validatedTrigger["module_id"] = $moduleId;
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing module ID.");

        return $validatedTrigger;

    }

    /*
     * Function that gets the necessary data that will be used to check the logic against
     * @params  integer  $id : starting-point identifier to search for source data
     * @params  integer  $sourceModuleId : the module id of the source content
     * @return  array : respective data based on module 
     * */
    public function getData($id, $sourceModuleId) {
        // $this->checkReadAccess();

        switch ($sourceModuleId) {
            case MODULE_QUESTIONNAIRE:
                $questionnaire = new Questionnaire(true);
                return $questionnaire->getQuestionnaireResults($id); /// ultimately there's a stored procedure for this
                break;
            
            default:
                return array();
                break;
        }
        
    }

    /*
     * Use 3rd party package to check logic in json format 
     * @params  array  $trigger : current trigger entry
     * @params  array  $dataToCheck : data to apply logic test to
     * @return  mixed : can be boolean/array depending on input test 
     * */
    public function checkLogic($trigger, $dataToCheck) {
        return JWadhams\JsonLogic::apply( json_decode($trigger["onCondition"], true), $dataToCheck );
    }

    /*
     * Execute event method when trigger logic passes 
     * @params  array  $trigger : current trigger entry
     * @params  integer  $patientId : patient id 
     * @return  
     * */
    public function triggerEvent($trigger, $patientId) {
        switch ($trigger['eventType']) {
            case TRIGGER_EVENT_PUBLISH: // only one for now
                $this->publish($trigger, $patientId);
                break;
            
            default:
                # code...
                break;
        }
    }

    /*
     * An event type method for publishing (i.e. inserting) content
     * @params  array  $trigger : current trigger entry
     * @params  integer  $patientId : patient id 
     * @return  
     * */
    public function publish($trigger, $patientId) {
        switch ($trigger["targetModuleId"]) {
            case MODULE_QUESTIONNAIRE:
                //$this->opalDB->publishQuestionnaire($trigger["targetContentId"], $patientId); // commented out for now
                echo "PUBLISHED QUESTIONNAIRE!";
                break;
            
            case MODULE_ALERT:
                // Need an alert table for publishing alerts
                break;
            
            case MODULE_EDU_MAT:
                //$this->opalDB->publishEducationalMaterial($trigger["targetContentId"], $patientId); // Not done yet
                break;

            case MODULE_POST:
                // Need to separate post 
                break;
            
            default:
                # code...
                break;
        }

    }


    /*
     * Main method to process triggers
     * @params  array  $postdata : POST data containing a starting-point identifier
     * @params  integer  $sourceModuleId : the module id of the source content 
     * @return  boolean : true if end of method is reached 
     * */
    public function executeTrigger($postData, $sourceModuleId) {

        $validatedData = $this->_validateTrigger($postData, $sourceModuleId);
        $id = $validatedData["id"];
        $sourceModuleId = $validatedData["module_id"];

        $sourceContentId = "";
        $patientId = ""; 

        $dataToCheck = $this->getData($id, $sourceModuleId); 

        if (!empty($dataToCheck)) {
            switch ($sourceModuleId) {
                case MODULE_QUESTIONNAIRE:
                    $sourceContentId = $dataToCheck["questionnaire_id"]; // to pull triggers related to this questionnaire
                    $patientId = $dataToCheck["patient_id"]; // which patient to trigger event on
                    break;

                default:
                    break;
            }
        }
        
        // Retrieve all triggers 
        $triggers = $this->getTriggers($sourceContentId, $sourceModuleId);

        foreach ($triggers as $index => $trigger) {
            if($this->checkLogic($trigger, $dataToCheck)) { // if trigger should be fired
                $this->triggerEvent($trigger, $patientId); 
            }
        }

        return true;
    }

 }
