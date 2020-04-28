<?php

/*
 * Study class objects and method
 * */

class Study extends OpalProject {

    /*
     * This function returns the list of available studies for opalAdmin.
     * TODO add lazy loading with pagination
     * @params void
     * @return  array of studies
     * */
    public function getStudies() {
        return $this->opalDB->getStudiesList();
    }


}