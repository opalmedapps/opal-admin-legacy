<?php
/**
 * User: Dominic Bourdua
 * Date: 5/8/2019
 * Time: 8:46 AM
 */

class QuestionnaireModule extends OpalProject
{
    protected $questionnaireDB;

    public function __construct($OAUserId = false) {
        parent::__construct($OAUserId);

        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD,
            $OAUserId
        );

        $this->questionnaireDB->setUsername($this->opalDB->getUsername());
        $this->questionnaireDB->setOAUserId($this->opalDB->getOAUserId());
        $this->questionnaireDB->setUserRole($this->opalDB->getUserRole());
    }

    /*
     * Function to sort options by their order value. Only being used when a question type has a list of options to sort
     * */
    protected static function sort_order($a, $b){
        if (intval($a["order"]) == intval($b["order"])) return 0;
        return (intval($a["order"]) < intval($b["order"])) ? -1 : 1;
    }

    protected static function sortOptions(&$options) {
        usort($options, 'self::sort_order');
        $cpt = 0;
        foreach($options as &$row) {
            $cpt++;
            $row["order"] = $cpt;
        }
    }
}