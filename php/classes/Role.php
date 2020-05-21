<?php

/*
 * Rolse class objects and method
 * */

class Role extends OpalProject {

    /*
     * This function returns the list of available studies for opalAdmin.
     * TODO add lazy loading with pagination
     * @params void
     * @return  array of studies
     * */
    public function getRoles() {
        return $this->opalDB->getRoles();
    }
}