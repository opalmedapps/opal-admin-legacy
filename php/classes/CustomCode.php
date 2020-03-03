<?php


class CustomCode extends OpalProject {

    /*
     * Get the list of all custom codes
     * */
    function getCustomCodes() {
        $results = $this->opalDB->getCustomCodes();
        //print_r($results);
        return $results;
    }
}