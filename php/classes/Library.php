<?php
/**
 * User: Dominic Bourdua
 * Date: 4/16/2019
 * Time: 1:55 PM
 */

class Library extends QuestionnaireModule {

    public function getLibraries() {
        return $this->questionnaireDB->fetchAllLibraries();
    }

    public function insertLibrary($newLibrary) {
        $nameEn = strip_tags($newLibrary["name_EN"]);
        $nameFr = strip_tags($newLibrary["name_FR"]);
        $private = strip_tags($newLibrary["private"]);

        $contentId = $this->questionnaireDB->addToDictionary(array(FRENCH_LANGUAGE=>$nameFr, ENGLISH_LANGUAGE=>$nameEn), TEMPLATE_QUESTION_TABLE);

        $toInsert = array(
            "name"=>$contentId,
            "private"=>$private,
        );

        return $this->questionnaireDB->addToLibraryTable($toInsert);
    }
}