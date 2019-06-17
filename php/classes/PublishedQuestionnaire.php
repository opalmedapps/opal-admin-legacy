<?php
/**
 * User: Dominic Bourdua
 * Date: 6/12/2019
 * Time: 8:47 AM
 */

class PublishedQuestionnaire extends Questionnaire {

    public function __construct($OAUserId = false, $sessionId = false) {
        parent::__construct($OAUserId);

        $this->opalDB->setSessionId($sessionId);
    }

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
            $row["triggers"] = $this->opalDB->getFilters($row["serial"]);
            $row["occurrence"] = $occurrenceArray;
        }
        return $publishedQuestionnaires;
    }

    /*
     * Validate and saniteze the list of published flags for questionnaire
     * @params  array of questionnaire to mark as published or not ($_POST)
     * @return  array of sanitize data
     * */

    function validateAndSanitizePublicationList($toValidate) {
        $validatedList = array();
        foreach($toValidate as $questionnaire) {
            $id = trim(strip_tags($questionnaire["serial"]));
            $publish = intval(trim(strip_tags($questionnaire["publish"])));
            if ($publish != 0 && $publish != 1)
                $publish = 0;
            array_push($validatedList, array("serial"=>$id, "publish"=>$publish));
        }
        return $validatedList;
    }

    /*
     * Update the status of a series of questionnaires (published = 1 / unpublished = 0)
     * @params  array of ID with the publication flag
     *          for example:    array(
     *                              array("serial"=>1, "publish"=>0),
     *                              array("serial"=>2, "publish"=>1),
     *                              array("serial"=>3, "publish"=>0),
     *                              ...
     *                          )
     * @return  void
     * */
    function updatePublicationFlags($list) {
        foreach($list as $row) {
            $this->opalDB->updatePublicationFlags($row["serial"], $row["publish"]);
        }
    }
}