<?php
/**
 * This class is where we should store all useful functions for the opalAdmin. Create static functions here.
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 11:12 AM
 */

class HelpSetup {
    /*
     * Basic functions to return an error message to the caller. Note: we have to define http_response_code if it
     * does not exists because some of our server are still running PHP 5.3 (a shame, yes).
     * @params  $errCode (int) error code to return. By default, 500
     *          $details (string) error message to display
     * @return  void
     * */
    public static function returnErrorMessage($errcode = HTTP_STATUS_INTERNAL_SERVER_ERROR, $details = "") {
        if (!function_exists('http_response_code')) {
            function http_response_code($newcode = NULL){
                static $code = HTTP_STATUS_SUCCESS;
                if($newcode !== NULL) {
                    header('X-PHP-Response-Code: '.$newcode, true, $newcode);
                    if(!headers_sent())
                        $code = $newcode;
                }
                return $code;
            }
        }

        header('Content-Type: application/javascript');
        http_response_code($errcode);
        if ($details != "")
            echo json_encode($details);
        die();
    }

    public static function makeSessionId($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
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

    /*
     * This function validates an Unix timestamp
     * @params  $timestamp (any) the value to validate.
     * @returns boolean true or false if timestamp
     * */
    public static function isValidTimeStamp($timestamp){
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    /*
     * Recursive function that sanitize the data
     * @params  array to sanitize
     * @return  array sanitized
     * */
    public static function arraySanitization($arrayForm) {
        $sanitizedArray = array();
        foreach($arrayForm as $key=>$value) {
            $key = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $key);
            if(is_array($value))
                $value = self::arraySanitization($value);
            else
                $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $value);
            $sanitizedArray[$key] = $value;
        }
        return $sanitizedArray;
    }

    /*
     * Generate a random string alpha-numerical. Default length of 256 characters
     * @params  $length : int (default 256)
     * @return  string : string of random characters of requested length.
     * */
    public static function generateRandomString($length = 256) {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!/$%?&*()-=_+[]<>^;:'),1,$length);
    }

    /*
     * Validate if a requested operation is valid based on the authorized operation. It's a bit per bit comparison.
     * Per default, the comparison is done on 3 bits since the authorized operations are from left to rigth
     * "Delete | Write | Read".
     * @params  $authorized : int - The authorized operations
     *          $requested : int - The requested operations to test
     *          $length : int - The length to test - min value is 0. Default is 2.
     * */
    public static function validateBitOperation($authorized, $requested, $length = 2) {
        $correct = true;
        for($cpt = 0;$cpt<=$length;$cpt++) {
            if( !($authorized & (1 << $cpt)) && ($requested & (1 << $cpt)) ) {
                $correct = false;
                break;
            }
        }
        return $correct;
    }

    /*
     * Validate if the user can access the specific module in read access
     * @params  int : $moduleAccess - module to validate
     * @return  boolean - Does the user has the read access for the module
     * */
    public static function validateReadModule($moduleAccess) {
        return HelpSetup::validateBitOperation($_SESSION["userAccess"][$moduleAccess]["access"], ACCESS_READ);
    }

    /*
     * Validate if the user can access the specific module in write access
     * @params  int : $moduleAccess - module to validate
     * @return  boolean - Does the user has the write access for the module
     * */
    public static function validateWriteModule($moduleAccess) {
        return HelpSetup::validateBitOperation($_SESSION["userAccess"][$moduleAccess]["access"], ACCESS_READ_WRITE);
    }

    /*
     * Validate if the user can access the specific module in delete access
     * @params  int : $moduleAccess - module to validate
     * @return  boolean - Does the user has the delete access for the module
     * */
    public static function validateDeleteModule($moduleAccess) {
        return HelpSetup::validateBitOperation($_SESSION["userAccess"][$moduleAccess]["access"], ACCESS_READ_WRITE_DELETE);
    }
}