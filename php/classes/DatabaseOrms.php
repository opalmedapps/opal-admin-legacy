<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * ORMS SQL config file that contains config access, table names and SQL queries used in ORMS
 * User: Dominic Bourdua
 * Date: 26/02/2019
 * Time: 11:04 AM
 */

class DatabaseOrms extends DatabaseAccess {

    function getAppointmentForAlias() {
        $result = $this->_fetchAll(ORMS_SQL_GET_APPOINTMENT_FOR_ALIAS, array());
        $toInsert = array();

        foreach($result as $item) {
            $tempArr = array(
                "externalId"=>-1,
                "type"=>2,
                "code"=>$item["code"],
                "expression"=>$item["expression"],
                "source"=>2,
            );
            array_push($toInsert, $tempArr);
        }
        return $toInsert;
    }
}
