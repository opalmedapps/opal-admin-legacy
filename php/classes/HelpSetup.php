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
        http_response_code($errcode);
        header('Content-Type: application/javascript');
        echo json_encode($details);
        die();
    }

    /*
     * This function validates a specific date and if it is still valid or not depending a format specified
     * @params  $date (string) the date itself.
     *          $strict (boolean). By default false. Determine if the date can be in the past or not
     *          $format (string). Format of the date to validate. By default "YYYY-mm-dd HH:ii"
     * @returns boolean true or false if valid or not
     * */
    public static function verifyDate($date, $strict = false, $format = 'Y-m-d H:i') {
        $dateTime = DateTime::createFromFormat($format, $date);
        $errors = DateTime::getLastErrors();
        if (!empty($errors['warning_count']))
            return false;

        if($strict) {
            if (new DateTime() > $dateTime)
                return false;
        }
        return $dateTime !== false;
    }
}