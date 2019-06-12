<?php
/**
 * User: Dominic Bourdua
 * Date: 6/12/2019
 * Time: 8:47 AM
 */

class PublishedQuestionnaire extends Questionnaire {

    /*
     * This function returns all the published questionnaires from the Questionnaire Control
     * @params  void
     * @return  array of published questionnaires
     * */
    public function getPublishedQuestionnaires() {
        $occurrenceArray = array(
            'start_date' => null,
            'end_date' => null,
            'set' => 0,
            'frequency' => array (
                'meta_key' => null,
                'meta_value' => null,
                'additionalMeta' => array()
            )
        );

        $publishedQuestionnaires = $this->opalDB->getPublishedQuestionnaires();
        foreach($publishedQuestionnaires as &$row) {
            $titles = $this->questionnaireDB->getQuestionnaireNames($row["db_serial"]);
            $row["expression_EN"] = $titles["title_EN"];
            $row["expression_FR"] = $titles["title_FR"];
            $row["triggers"] = $this->opalDB->getFilters($row["QuestionnaireControlSerNum"]);
            $row["occurrence"] = $occurrenceArray;
        }
        return $publishedQuestionnaires;
    }
}