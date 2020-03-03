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

    /*
     * gets the list of modules availables where adding custom codes
     * @params  void
     * @return  array of modules
     * */
    public function getAvailableModules() {
        return $this->opalDB->getAvailableModules();
    }

}