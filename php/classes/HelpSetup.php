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
        if (!function_exists('http_response_code'))
        {
            function http_response_code($newcode = NULL)
            {
                static $code = HTTP_STATUS_SUCCESS;
                if($newcode !== NULL)
                {
                    header('X-PHP-Response-Code: '.$newcode, true, $newcode);
                    if(!headers_sent())
                        $code = $newcode;
                }
                return $code;
            }
        }

        header('Content-Type: application/javascript');
        http_response_code($errcode);
        echo json_encode($details);
        die();
    }
}