<?php
/**
 * This class is where we should store all useful functions for the opalAdmin. Create static functions here.
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 11:12 AM
 */

class HelpSetup {

    /*
     * The following constants are used by the database class to manually insert the creation date by creating an array
     * of exception fields. WARNING!!! THIS METHOD BYPASS THE BINDPARAM METHOD OF PHP AND CAN CAUSE A SERIOUS SECURITY
     * RISK! ONLY USE IT IF YOU HAVE THE APPROVAL OF THE TEAM!
     * */
    const CREATION_DATE_FIELD = "creationDate";
    const EXCEPTION_FIELDS = array(HelpSetup::CREATION_DATE_FIELD);

    /*
     * Basic functions to return an error message to the caller
     * */
    public static function returnErrorMessage($errcode, $details) {
        header('Content-Type: application/javascript');
        $response['code'] = $errcode;
        $response['message'] = $details;
        echo json_encode($response);
        die();
    }
}