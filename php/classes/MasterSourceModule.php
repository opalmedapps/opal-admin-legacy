<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

class MasterSourceModule extends Module {

    /**
     * MasterSourceModule constructor.
     * @param false $guestStatus
     */
    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_MASTER_SOURCE, $guestStatus);
    }

    /**
     * Get the list of all active database sources (i.e. not local)
     * @return array - list of activate database sources with ID and name
     */
    public function getExternalSourceDatabase() {
        $this->checkReadAccess();
        return $this->opalDB->getExternalSourceDatabase();
    }

    /**
     * This method validate the three fields used to identify a record uniquely without the primary key.
     * @param $errCode string   error code. Error validation code is coded as an int of 3 bits (value from 0 to 7). Bit
     *                          informations are coded from right to left:
     *                          1: source invalid or missing
     *                          2: externalId invalid or missing
     *                          3: code invalid or missing
     * @param $post array       key fields to validate
     */
    protected function _validateKeyFields(&$errCode, &$post) {

        if(is_array($post)) {
            if(!array_key_exists("source", $post) || $post["source"] == "")
                $errCode = "1" . $errCode;
            else {
                $data = $this->opalDB->getSourceId($post["source"]);
                if(count($data) != 1)
                    $errCode = "1" . $errCode;
                else {
                    $post["source"] = $data[0]["ID"];
                    $errCode = "0" . $errCode;
                }
            }
            if(!array_key_exists("externalId", $post) || $post["externalId"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
            if(!array_key_exists("code", $post) || $post["code"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;
        }
        else
            $errCode = "111";
    }
}
