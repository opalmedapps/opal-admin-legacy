<?php

// SPDX-FileCopyrightText: Copyright (C) 2019 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/*
 * This class inherits all methods and structures from DatabaseAccess and is used to access the Aria system
 * User: Dominic Bourdua
 * Date: 17/12/2019
 * Time: 11:08 AM
 * */

class DatabaseAria extends DatabaseAccess {

    function getAllAliasesToInsert() {

       $firstResult = $this->_fetchAll(ARIA_GET_ALIASES_QT, array());
       $secondResult = $this->_fetchAll(ARIA_GET_ALIASES_DOC, array());
       $toInsert = array();

       foreach($firstResult as $item) {
           $tempArr = array(
               "externalId"=>$item["ID"],
               "type"=>$item["type"],
               "code"=>$item["code"],
               "expression"=>$item["expression"],
               "source"=>1,
           );
           array_push($toInsert, $tempArr);
       }
       foreach($secondResult as $item) {
           $tempArr = array(
               "externalId"=>$item["ID"],
               "type"=>$item["type"],
               "code"=>$item["Name"],
               "expression"=>$item["Name"],
               "source"=>1,
           );
           array_push($toInsert, $tempArr);
       }
        return $toInsert;
    }
}
