<?php
/**
 * This class is where we should store all useful functions for the opalAdmin. Create static functions here.
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 11:12 AM
 */

class HelpSetup {

    const OPAL_OAUSER_ROLE_ADMIN = "1";
    const OPAL_OAUSER_ROLE_MANAGER = "6";
    const AUTHORIZATION_MODIFICATION_FINALIZED = array(HelpSetup::OPAL_OAUSER_ROLE_ADMIN, HelpSetup::OPAL_OAUSER_ROLE_MANAGER);

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