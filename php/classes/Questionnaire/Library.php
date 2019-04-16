<?php
/**
 * User: Dominic Bourdua
 * Date: 4/16/2019
 * Time: 1:55 PM
 */

class Library
{
    protected $questionnaireDB;
    protected $opalDB;

    public function __construct($userId = "-1") {
        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD
        );
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD
        );

        $this->setUserInfo($userId);
    }

    protected function setUserInfo($userId) {
        $userInfo = $this->opalDB->getUserInfo($userId);
        $this->opalDB->setUserId($userInfo["userId"]);
        $this->opalDB->setUsername($userInfo["username"]);
        $this->questionnaireDB->setUserId($userInfo["userId"]);
        $this->questionnaireDB->setUsername($userInfo["username"]);
    }

    public function getLibraries() {
        return $this->questionnaireDB->fetchAllLibraries();
    }

    public function insertLibrary($newLibrary) {
        $nameEn = strip_tags($newLibrary["name_EN"]);
        $nameFr = strip_tags($newLibrary["name_FR"]);
        $private = strip_tags($newLibrary["private"]);

        $contentId = $this->questionnaireDB->addToDictionary(array(FRENCH_LANGUAGE=>$nameFr, ENGLISH_LANGUAGE=>$nameEn), TYPE_TEMPLATE_TABLE);

        $toInsert = array(
            "OAUserId"=>$this->questionnaireDB->getUserId(),
            "name"=>$contentId,
            "private"=>$private,
        );

        return $this->questionnaireDB->addToLibraryTable($toInsert);
    }
}