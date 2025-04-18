<?php

// SPDX-FileCopyrightText: Copyright (C) 2022 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * TriggerStaff class
 */
class TriggerStaff extends Trigger
{

    /**
     * Validate the input parameters
     * Validation code :
     *                      1st bit source system invalid or missing
     *
     * @param array<mixed> $post (Reference) - document parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @return string $errCode - error code
     */
    protected function _validateSourceExternalId(&$post,  &$source)
    {

        $errCode = "";

        // 1st bit - source system
        if (!array_key_exists("source", $post) || $post["source"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $source = $this->opalDB->getSourceDatabaseDetails($post["source"]);
            if (count($source) != 1) {
                $source = array();
                $errCode = "1" . $errCode;
            } else {
                $source = $source[0];
                $errCode = "0" . $errCode;
            }
        }

        return $errCode;
    }

    /**
     * Validate the input parameters for individual staff information
     * Validation code :
     *                      1st bit invalid or missing source system
     *                      2nd bit invalid or missing user ID
     *                      3rd bit invalid or missing first name
     *                      4th bit invalid or missing last name
     *
     * @param array<mixed> $post (Reference) - staff parameters
     * @param array<mixed> &$source (Reference) - source parameters
     * @return string $errCode - error code.
     */
    protected function _validateStaff(&$post,  &$source)
    {
        $errCode = $this->_validateSourceExternalId($post, $source);

        if (bindec($errCode) != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, json_encode(array("validation" => $errCode)));

        //bit 5
        if (!array_key_exists("userId", $post) || $post["userId"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        //bit 5
        if (!array_key_exists("firstName", $post) || $post["firstName"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        //bit 5
        if (!array_key_exists("lastName", $post) || $post["lastName"] == "") {
            $errCode = "1" . $errCode;
        } else {
            $errCode = "0" . $errCode;
        }

        return $errCode;
    }

    /**
     * Insert or update staff resource after validation.
     * @param  $post - array - contains document details
     * @return void
     */
    public function updateStaff($post)
    {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $this->_updateStaff($post);
    }

    /**
     * This function insert or update a staff information after its validation.
     * @param  $post : array - details of staff information to insert/update.
     * @return  void
     */
    protected function _updateStaff(&$post)
    {

        $source = null;

        $errCode = $this->_validateStaff($post, $source);
        $errCode = bindec($errCode);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $staff = $this->opalDB->getStaff($post["userId"],$source["SourceDatabaseSerNum"]);

        $staffData = array(
            "SourceDatabaseSerNum" => $source["SourceDatabaseSerNum"],
            "StaffId" => $post["userId"],
            "LastName" => $post["lastName"],
            "FirstName" => $post["firstName"],
            "LastUpdated" => $post["lastUpdated"],
        );

        if ($staff !== false) {
            $staffData = $staff;
        }

        $staffData["LastName"] = $post["lastName"];
        $staffData["FirstName"] = $post["firstName"];
        $staffData["LastUpdated"] = $post["lastUpdated"];

        if ($staff === false) {
            $this->opalDB->insertStaff($staffData);
        } else {
            $this->opalDB->updateStaff($staffData);
        }
    }
}
