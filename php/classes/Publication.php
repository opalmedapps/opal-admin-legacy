<?php


class Publication extends OpalProject
{

    /*
     * Return the list of all available publications
     * params   none
     * returns  array of data
     * */
    public function getPublications() {
        return $this->opalDB->getPublications();
    }

    /*
     * Get the list of materials that can be published based on the module request
     * params   module ID
     * returns  array of data
     * */
    public function getPublicationsPerModule($moduleId) {
        $results = $this->opalDB->getPublicationsPerModule($moduleId);
        $tempArray = array();
        foreach($results["triggers"] as $trigger) {
            array_push($tempArray, $trigger["triggerSettingId"]);
        }
        $results["triggers"] = $tempArray;
        return $results;
    }

    public function getPublicationChartLogs() {
        $result = $this->opalDB->getPublicationChartLogs();

        $arrResult = array();
        $tempResult = array();

        $currentModule = "-1";
        $currentID = "-1";
        foreach($result as $row) {
           // print_r($row); print "<br/><br/>";
            if($currentModule != $row["moduleId"] || $currentID != $row["ID"]) {
                if (!empty($tempResult))
                    array_push($arrResult, array("name"=>$row["name_EN"], "data"=>$tempResult));
                $tempResult = array();
                $currentModule = $row["moduleId"];
                $currentID = $row["ID"];
            }
            array_push($tempResult, array("x"=>$row["x"],"y"=>$row["y"],"cron_serial"=>$row["cron_serial"]));
        }
        array_push($arrResult, array("name"=>$row["name_EN"], "data"=>$tempResult));
        return $arrResult;
    }

    /*
     * Validate and sanitize the list of publish flag of publications
     * @params  array of publications to mark as published or not ($_POST)
     * @return  array of sanitize data
     * */
    function validateAndSanitizePublicationList($toValidate) {
        $validatedList = array();
        foreach($toValidate as $item) {
            $id = trim(strip_tags($item["ID"]));
            $publication = trim(strip_tags($item["moduleId"]));
            $publishFlag = intval(trim(strip_tags($item["publishFlag"])));
            if (publishFlag != 0 && publishFlag != 1)
                $publishFlag = 0;
            array_push($validatedList, array("ID"=>$id, "moduleId"=>$publication, "publishFlag"=>$publishFlag));
        }
        return $validatedList;
    }

    /*
     * Update the status of a series of publications (published = 1 / unpublished = 0)
     * @params  array of ID with the publication flag
     *          for example:    array(
     *                              array("serial"=>1, "publish"=>0),
     *                              array("serial"=>2, "publish"=>1),
     *                              array("serial"=>3, "publish"=>0),
     *                              ...
     *                          )
     * @return  void
     * */
    function updatePublicationFlags($list) {
        $publicationModules = $this->opalDB->getPublicationModules();
        foreach($list as $row) {
            foreach($publicationModules as $module) {
                if ($module["ID"] == $row["moduleId"]) {
                    $this->opalDB->updatePublicationFlag($module["tableName"], $module["primaryKey"], $row["publishFlag"], $row["ID"]);
                    break;
                }
            }
        }
    }

}