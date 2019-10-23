<?php


class Publication extends OpalProject
{

    /*
     * Return the list of all available publications
     * params   none
     * return#  array of data
     * */
    public function getPublications() {
        return $this->opalDB->getPublications();
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
                    $this->opalDB->updatePublicationFlag($module["tableName"], $module["primaryKeyName"], $row["publishFlag"], $row["ID"]);
                    break;
                }
            }
        }
    }

}