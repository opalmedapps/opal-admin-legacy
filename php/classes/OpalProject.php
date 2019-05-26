<?php
/**
 * User: Dominic Bourdua
 * Date: 5/8/2019
 * Time: 8:44 AM
 */

class OpalProject
{
    protected $opalDB;

    /*
     * constructor of the class
     * */
    public function __construct($OAUserId = false) {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            $OAUserId
        );
    }
}