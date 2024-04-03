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

    /**
     * @param int $length
     * @return string
     */
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

        $currentDate = new DateTime();
        $errors = DateTime::getLastErrors();

        if (!empty($errors['warning_count']))
            return false;

        if($strict) {
            //var_dump($dateTime);
            //var_dump($currentDate->format($format));
            if ($currentDate->format($format) >= $dateTime)
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
        if(!is_array($arrayForm))
            return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $arrayForm);
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
     * Per default, the comparison is done on 3 bits since the authorized operations are from left to right
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

    /**
     * Prepares the navigation menu for an user with a specific language.
     * @param $userMenu : array - current navigation menu
     * @param $language : string - language of the user
     * @return mixed : array - correct structure and language of the navigation menu
     */
    public static function prepareNavMenu($userMenu, $language) {
        $newMenu = $userMenu;

        foreach($newMenu as &$category) {
            if(strtoupper($language) == "FR")
                $category["name"] = $category["name_FR"];
            else
                $category["name"] = $category["name_EN"];
            unset($category["name_FR"]);
            unset($category["name_EN"]);
            foreach($category["menu"] as &$menu) {
                if(strtoupper($language) == "FR")
                    $menu["name"] = $menu["name_FR"];
                else
                    $menu["name"] = $menu["name_EN"];
                unset($menu["name_FR"]);
                unset($menu["name_EN"]);
            }
        }
        return $newMenu;
    }

    /**
     * Returns the real IP address of an user. It checks the IP from the Internet, check if it passed from a proxy, or
     * as a last result, get the IP from the remote address.
     * @return mixed - IP address of the user.
     */
    public static function getUserIP(){
        if ( !empty($_SERVER['HTTP_CLIENT_IP']) ) {
            // Check IP from internet.
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
            // Check IP is passed from proxy.
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            // Get IP address from remote address.
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Stores the module name and method called. This function is trickier than the other, since there is no return
     * function: the variables are passed through
     * reference.
     * @param $moduleName - name of the main module that made the call
     * @param $methodeName - name of the method that made the call
     */
    public static function getModuleMethodName(&$moduleName, &$methodeName) {
        $debugBackTrace = debug_backtrace();
        $methodeName =  $debugBackTrace[count($debugBackTrace) - 1]["function"];
        $moduleName = $debugBackTrace[count($debugBackTrace) - 1]["class"];
    }

    /**
     * Validate the users email
     * @param (str) email
     * @return (mixed) filtered data for valid false for invalid
     */
    public static function validateEmail($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate the users phone number (+xx optional, 10 digit number)
     * @param (str) phone number
     * @return (int) 1 for valid 0 for invalid
     */
    public static function validatePhone($phone){
        return preg_match('/^(\+\d{0,2})?(\d{10})$/', $phone);
    }

    /**
     * Validate the users phone extension (0 to 5 digits)
     * @param (str) phone extension
     * @return (int) 1 for valid 0 for invalid
     */
    public static function validatePhoneExt($phoneExt){
        return preg_match('/^\d{0,6}$/', $phoneExt);
    }

    public static function filePutContents($dir, $contents){
        $parts = explode('/', $dir);
        $file = array_pop($parts);
        $dir = '';
        foreach($parts as $part)
            if(!is_dir($dir .= "/$part")) {
                echo "$dir $part\r\n";

                if (mkdir($dir, 0774)) {
                    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot create folder");
                }
            }
        if(file_put_contents("$dir/$file", $contents) == false)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot create file");
    }
}