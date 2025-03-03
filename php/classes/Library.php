<?php

// SPDX-FileCopyrightText: Copyright (C) 2019 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * User: Dominic Bourdua
 * Date: 4/16/2019
 * Time: 1:55 PM
 */

class Library extends QuestionnaireModule {

    public function getLibraries() {
        $this->checkReadAccess();
        return $this->questionnaireDB->fetchAllLibraries();
    }

    public function insertLibrary($newLibrary) {
        $this->checkWriteAccess($newLibrary);
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