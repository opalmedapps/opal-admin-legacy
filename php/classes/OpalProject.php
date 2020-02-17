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
    public function __construct($OAUserId = false, $sessionId = false) {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            $OAUserId
        );
        $this->opalDB->setSessionId($sessionId);
    }

    /*
     * gets the list of modules availables
     * @params  void
     * @return  array of modules
     * */
    public function getPublicationModulesUser() {
        return $this->opalDB->getPublicationModulesUser();
    }

    /*
     * Get the details of a module
     * @param   $moduleId (int) ID of the module
     * @return  array of details of the module
     * */
    public function getPublicationModuleUserDetails($moduleId) {
        return $this->opalDB->getPublicationModuleUserDetails();
    }


    /*
     * Get the chart logs of a specific post
     * @param   $serial (int) SerNum of the Post
     *          $type (string) type of post
     * @return  $data (array) array of chart log results.
     * */
    public function getPostChartLogs($serial, $type) {
        $data = array();
        if($serial == "" || $type == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid settings for chart log.");

        if ($type == 'Announcement')
            $result = $this->opalDB->getAnnouncementChartLogs($serial);
        else if ($type == 'Treatment Team Message')
            $result = $this->opalDB->getTTMChartLogs($serial);
        else if ($type == 'Patients for Patients')
            $result = $this->opalDB->getPFPChartLogs($serial);
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Unknown type of post.");

        //The Y value has to be converted to an int, or the chart log will reject it on the front end.
        foreach ($result as &$item) {
            $item["y"] = intval($item["y"]);
        }

        if (count($result) > 0)
            array_push($data, array("name"=>$type, "data"=>$result));

        return $data;
    }

    public function getEducationalChartLogs($serial, $type) {
        $data = array();
        if($serial == "" || $type == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid settings for chart log.");

        $result = $this->opalDB->getEducationalChartLogs($serial);

        //The Y value has to be converted to an int, or the chart log will reject it on the front end.
        foreach ($result as &$item) {
            $item["y"] = intval($item["y"]);
        }

        if (count($result) > 0)
            array_push($data, array("name"=>$type, "data"=>$result));

        return $data;
    }

    /*
     * Recursive function that sanitize the data
     * @params  array to sanitize
     * @return  array sanitized
     * */
    function arraySanitization($arrayForm) {
        $sanitizedArray = array();
        foreach($arrayForm as $key=>$value) {
            $key = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $key);
            if(is_array($value))
                $value = $this->arraySanitization($value);
            else
                $value = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $value);
            $sanitizedArray[$key] = $value;
        }
        return $sanitizedArray;
    }
}