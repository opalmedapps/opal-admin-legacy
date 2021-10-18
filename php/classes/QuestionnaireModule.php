<?php
/**
 * User: Dominic Bourdua
 * Date: 5/8/2019
 * Time: 8:46 AM
 */

class QuestionnaireModule extends Module
{
    protected $questionnaireDB;

    public function __construct($OAUserId = false, $sessionId = false) {
        parent::__construct(MODULE_QUESTIONNAIRE, $OAUserId);

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

    /*
     * Function to sort options by their order value. Only being used when a question type has a list of options to sort
     * */
    protected static function sort_order($a, $b){
        if (intval($a["order"]) == intval($b["order"])) return 0;
        return (intval($a["order"]) < intval($b["order"])) ? -1 : 1;
    }

    /* Sort function based on the order field */
    protected static function sortOptions(&$options) {
        usort($options, 'self::sort_order');
        $cpt = 0;
        foreach($options as &$row) {
            $cpt++;
            $row["order"] = $cpt;
        }
    }
    
    protected function validateSliderForm($sliderOptions) {
        $sliderOptions["minValue"] = floatval($sliderOptions["minValue"]);
        $sliderOptions["maxValue"] = floatval($sliderOptions["maxValue"]);
        $sliderOptions["increment"] = floatval($sliderOptions["increment"]);
        if($sliderOptions["increment"] <= 0)
            return false;
        $sliderOptions["maxValue"] = floatval(floor(($sliderOptions["maxValue"] - $sliderOptions["minValue"]) / $sliderOptions["increment"]) * $sliderOptions["increment"]) + $sliderOptions["minValue"];
        if ($sliderOptions["minCaption_EN"] == "" || $sliderOptions["minCaption_FR"] == "" || $sliderOptions["maxCaption_EN"] == "" || $sliderOptions["maxCaption_FR"] == "" || $sliderOptions["minValue"] <= 0.0 || $sliderOptions["maxValue"] <= 0.0 || $sliderOptions["minValue"] >= $sliderOptions["maxValue"])
            return false;
        return $sliderOptions;
    }
}