<?php


class CronJob extends OpalProject {

    protected $questionnaireDB;

    public function __construct() {
        if(!in_array(HelpSetup::getUserIP(), LOCALHOST_ADDRESS))
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access denied.");

        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            23,
            false
        );

        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD,
            false
        );

        $this->questionnaireDB->setUsername($this->opalDB->getUsername());
        $this->questionnaireDB->setOAUserId($this->opalDB->getOAUserId());
        $this->questionnaireDB->setUserRole($this->opalDB->getUserRole());
    }

    public function processResourcePending() {
        echo "this is a test\r\n";

        print_r($this->questionnaireDB->getFinalizedQuestions());


        die("end of the test");
    }
}