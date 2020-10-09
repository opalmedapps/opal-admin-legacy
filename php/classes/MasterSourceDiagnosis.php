<?php


class MasterSourceDiagnosis extends MasterSourceModule {

    /*
     * Get the list of all undeleted master diagnoses
     * @params  void
     * @return  array - List of master diagnoses
     * */
    public function getMasterSourceDiagnoses() {
        $this->checkReadAccess();
        return $this->opalDB->getMasterSourceDiagnoses();
    }
}