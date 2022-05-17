<?php


class HospitalMap extends Module {

    /**
     * Construct
     * @param $guestStatus boolean - default false. Determine is the user is a guest (not logged in) or not
     */
    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_HOSPITAL_MAP, $guestStatus);
    }

    /**
     * Validate and sanitize an hospital map.
     * @param $post array - data for the hospital map to validate
     * @param boolean $isAnUpdate - if the validation must include the ID of the hospital map or not
     * @param array $hospitalMap - if $isAnUpdate is setup to true, returns the current hospital map if it exists
     * Validation code :    Error validation code is coded as an int of 7 bits (value from 0 to 127). Bit information
     *                      are coded from right to left:
     *                      1: english name missing
     *                      2: french name missing
     *                      3: english description missing
     *                      4: french description missing
     *                      5: english URL missing
     *                      6: french URL missing
     *                      7: hospital map ID is missing or invalid if it is an update
     *
     * @return string $errCode - contains the invalid entries with an error code.
     */
    protected function _validateHospitalMap(&$post, $isAnUpdate = false, &$hospitalMap = array()) {
        $post = HelpSetup::arraySanitization($post);
        $errCode = "";

        if (is_array($post)) {

            if (!array_key_exists("name_EN", $post) || $post["name_EN"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 2nd bit
            if (!array_key_exists("name_FR", $post) || $post["name_FR"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 3rd bit
            if (!array_key_exists("description_EN", $post) || $post["description_EN"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 4th bit
            if (!array_key_exists("description_FR", $post) || $post["description_FR"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 5th bit
            if (!array_key_exists("url_EN", $post) || $post["url_EN"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 6th bit
            if (!array_key_exists("url_FR", $post) || $post["url_FR"] == "")
                $errCode = "1" . $errCode;
            else
                $errCode = "0" . $errCode;

            // 7th bit
            if($isAnUpdate) {
                if (!array_key_exists("serial", $post) || $post["serial"] == "")
                    $errCode = "1" . $errCode;
                else {
                    $hospitalMap = $this->opalDB->getHospitalMapDetails($post["serial"]);
                    if (count($hospitalMap) < 1)
                        $errCode = "1" . $errCode;
                    else if (count($hospitalMap) == 1) {
                        $hospitalMap = $hospitalMap[0];
                        $errCode = "0" . $errCode;
                    }
                    else
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates hospital maps found.");
                }
            } else
                $errCode = "0" . $errCode;

        } else
            $errCode .= "1111111";

        return $errCode;
    }

    /**
     * Add new hospital map after vadlidatin its informations. In case of error in the validation, returns error 400
     * with error validation.
     * @param $post array - contains the details of the hospital map
     */
    public function insertHospitalMap($post) {
        $this->checkWriteAccess($post);
        $errCode = $this->_validateHospitalMap($post);
        $errCode = bindec($errCode);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $toInsert = array(
            "MapName_EN"=>$post["name_EN"],
            "MapName_FR"=>$post["name_FR"],
            "MapDescription_EN"=>$post["description_EN"],
            "MapDescription_FR"=>$post["description_FR"],
            "MapUrl"=>$post["url_EN"],
            "MapURL_EN"=>$post["url_EN"],
            "MapURL_FR"=>$post["url_FR"],
        );

        $this->opalDB->insertHospitalMap($toInsert);
    }

    /**
     * Get the complete list of the hospital maps in Opal
     * TODO : pagination system
     * @return array - contains the list of hospital mal
     */
    public function getHospitalMaps() {
        $this->checkReadAccess();
        return $this->opalDB->getHospitalMaps();
    }

    /**
     * Validate the hospital map ID to insure it is neither empty or invalid. An error code is returned if the info
     * provided by the user is missing or invalid.
     * @param $post array - contains the ID of the hospital map
     * @param $hospitalMap array - will contain the hospital map if found
     * Validation code :    Error validation code is coded as an int of 2 bits (value from 0 to 3). Bit informations
     *                      are coded from right to left:
     *                      1: serial missing
     *                      2: no hospital map found
     *
     * @return string - contains the error code
     */
    protected function _validateHospitalMapDetails(&$post, &$hospitalMap) {
        $errCode = "";
        $post = HelpSetup::arraySanitization($post);
        if (is_array($post)) {
            $post["serial"] = intval(strip_tags($post["serial"]));
            if (!array_key_exists("serial", $post) || $post["serial"] == "")
                $errCode = "11" . $errCode;
            else {
                $errCode = "0" . $errCode;
                $post["serial"] = intval(strip_tags($post["serial"]));
                $hospitalMap = $this->opalDB->getHospitalMapDetails($post["serial"]);
                if (count($hospitalMap) < 1)
                    $errCode = "1" . $errCode;
                else if (count($hospitalMap) == 1) {
                    $errCode = "0" . $errCode;
                    $hospitalMap = $hospitalMap[0];
                }
                else
                    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates hospital maps found.");
            }
        } else
            $errCode = "11";

        return $errCode;
    }

    /**
     * Get the details of a specified hospital map
     * @param $post - containts hospital map ID
     * @return array $hospitalMap - contains hospital map found
     */
    public function getHospitalMapDetails($post) {
        $this->checkReadAccess($post);
        $hospitalMap = array();

        $errCode = $this->_validateHospitalMapDetails($post, $hospitalMap);
        $errCode = bindec($errCode);

        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        return $hospitalMap;
    }

    /**
     * Update an hospital map after its validation
     * @param $post array - contains the details of the hospital map
     */
    public function updateHospitalMap($post) {
        $this->checkWriteAccess($post);
        $hospitalMap = array();
        $errCode = $this->_validateHospitalMap($post, true, $hospitalMap);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $toUpdate = array(
            "HospitalMapSerNum"=>$post["serial"],
            "MapName_EN"=>$post["name_EN"],
            "MapName_FR"=>$post["name_FR"],
            "MapDescription_EN"=>$post["description_EN"],
            "MapDescription_FR"=>$post["description_FR"],
            "MapUrl"=>$post["url_EN"],
            "MapURL_EN"=>$post["url_EN"],
            "MapURL_FR"=>$post["url_FR"],
        );

        $this->opalDB->updateHospitalMap($toUpdate);
    }
}