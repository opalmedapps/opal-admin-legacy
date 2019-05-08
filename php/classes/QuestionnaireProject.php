<?php
/**
 * User: Dominic Bourdua
 * Date: 5/8/2019
 * Time: 8:46 AM
 */

class QuestionnaireProject extends OpalProject
{
    protected $questionnaireDB;


    public function __construct($userId = false) {
        parent::__construct($userId);

        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD,
            $userId
        );

        $this->questionnaireDB->setUsername($this->opalDB->getUsername());
        $this->questionnaireDB->setUserId($this->opalDB->getUserId());
        $this->questionnaireDB->setUserRole($this->opalDB->getUserRole());
    }
}