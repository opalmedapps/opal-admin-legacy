<?php

include('../lib/JWadhams/JsonLogic.php');
/*
 * Trigger class object and methods
 * */

class Trigger extends Module {

    protected $questionnaireDB;

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_TRIGGER, $guestStatus);
        // we instantiate this object for access to questionnaireDB in _getQuestionnaireResults function
        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD,
            false
        );
    }

    /*
     * Validate and sanitize post data and check if module id is set. If there is a problem return an error 422.
     * @params  array  $postdata : POST data containing a starting-point identifier
     * @params  integer  $sourceModuleId : the module id of the source content 
     * @return int $errCode : error code (if any error found. 0 = no error)
     * */
    protected function _validateTrigger(&$postData, &$moduleId) {
        $postData = HelpSetup::arraySanitization($postData);
        $errCode = "";

        if(is_array($postData)){
            //bit 1
            if(!array_key_exists("id", $postData) || $postData["id"] == ""){
                $errCode = "1" . $errCode;
            }else{
                $errCode = "0" . $errCode;
            }
            //bit 2
            // language field is optional so we must account for this
            if(array_key_exists("language", $postData)){
                if(in_array(strip_tags($postData["language"]), OPAL_ADMIN_LANGUAGES)){
                    $errCode = "0" . $errCode;
                }else{ //given language not currently supported
                    $errCode = "1" . $errCode;
                }
            }else{ //default to french if no language given
                $postData["language"] = ABVR_FRENCH_LANGUAGE;
            }
            //bit 3
            if(!in_array(strip_tags($moduleId), MODULE_PUBLICATION_TRIGGER)) { //in this case it is an internal issue and we just return 500 error immediately
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Unknown module. Please contact your admin with date and type of your request.");
            }

        }else{
            $errCode = "11";
        }

        return bindec($errCode);
    }

    /*
     * Gets questionnaire answers
     *
     * @param integer $patientQuestionnaireSer : the patient-questionnaire relation serial number
     * @return array questionnaire results along with explicit questionnaire ID and patientSerNum 
     */
    protected function _getQuestionnaireResults($patientQuestionnaireSer, $language){
        $questionnaireResults = $this->questionnaireDB->getQuestionnaireResults($patientQuestionnaireSer, $language);
        $questionnaireId = $questionnaireResults[0][0]["questionnaire_id"];
        $patientSerNum = $questionnaireResults[0][0]["externalId"]; // patient_id

        $currentAnswers = $questionnaireResults[3];

        // also need to get answers from the questionnaire just before the current (if exists)
        $prevQuestionnaire = $this->questionnaireDB->getLastAnsweredQuestionnaire($questionnaireId, $patientSerNum);
        $prevAnswers = array();
        if (!empty($prevQuestionnaire)) {
            $prevPatientQuestionnaireSer = $prevQuestionnaire[0]["PatientQuestionnaireSerNum"];
            $prevQuestionnaireResults = $this->questionnaireDB->getQuestionnaireResults($prevPatientQuestionnaireSer, $language);
            $prevAnswers = $prevQuestionnaireResults[3];
        }

        return array(
            "questionnaire_id"=>$questionnaireId,
            "patient_ser"=>$patientSerNum,
            "answers"=>array("current"=>$currentAnswers, "previous"=>$prevAnswers)
        );

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
               $result = $this->_getQuestionnaireResults($id, $language); // this wont work as is because Module provides OpalDB access, not QuestionnaireDB access
                break;
            default: //return a 500 error if moduleId has no case matches
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Unknown module or no case matches. Please contact your admin with date and type of your request.");

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
            default: //in this case return error 500 because something went wrong on backend with retrieving triggers
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Unknown trigger. Please contact your admin with date and type of your request.");
        }
        return $result;
    }

    /*
     * An event type method for publishing (i.e. inserting) content
     * @params  array  $trigger : current trigger entry
     * @params  integer  $patientSerNum : patient serial
     * @return  integer id of the record inserted
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
            default:
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Unknown trigger. Please contact your admin with date and type of your request.");
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
        $this->checkWriteAccess($postData);
        $eventTriggers = array();
        $errCode = $this->_validateTrigger($postData, $sourceModuleId);
        if($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, json_encode(array("validation"=>$errCode)));

        $sourceContentId = "";
        $patientSerNum = "";

        $dataToCheck = $this->_getData($postData["id"], $sourceModuleId, $postData["language"]); //get relevant questionnaire id based on patientQuestionnaireSerNum

        if (!empty($dataToCheck)) {
            switch ($sourceModuleId) {
                case MODULE_QUESTIONNAIRE:
                    $sourceContentId = $dataToCheck["questionnaire_id"]; // to pull triggers related to this questionnaire
                    $patientSerNum = $dataToCheck["patient_ser"]; // which patient to trigger event on
                    break;
                default:
                    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Unknown module. Please contact your admin with date and type of your request.");
            }
        }
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR, json_encode(array("validation"=>4)));


        // Retrieve all triggers
        $triggers = $this->opalDB->getTriggersList($sourceContentId, $sourceModuleId); //any relevant triggers for this questionnaire
        foreach ($triggers as $index => $trigger) {
            if(JWadhams\JsonLogic::apply( json_decode($trigger["onCondition"], true), $dataToCheck )) { // if trigger should be fired
                $eventResponse = $this->_triggerEvent($trigger, $patientSerNum);
                if ($eventResponse)
                    array_push($eventTriggers, $eventResponse);
            }
        }

        return $eventTriggers;
    }
}
