<?php
/**
 * This class is where we should store all useful functions for the opalAdmin. Create static functions here.
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 11:12 AM
 */

class HelpSetup {
    /*
     * Basic functions to return an error message to the caller
     * */
    public static function returnErrorMessage($errcode = HTTP_STATUS_INTERNAL_SERVER_ERROR, $details) {
        http_response_code($errcode);
        header('Content-Type: application/javascript');
        echo json_encode($details);
        die();
    }
}