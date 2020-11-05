<?php


class MasterSourceModule extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_MASTER_SOURCE, $guestStatus);
    }

    /*
     * Get the list of all active database sources (i.e. not local)
     * @params  void
     * @return  array - list of activate database sources with ID and name
     * */
    public function getExternalSourceDatabase() {
        $this->checkReadAccess();
        return $this->opalDB->getExternalSourceDatabase();
    }
}