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
    public static function returnErrorMessage($errcode, $details) {
        header('Content-Type: application/javascript');
        $response['code'] = $errcode;
        $response['message'] = $details;
        echo json_encode($response);
        die();
    }
}