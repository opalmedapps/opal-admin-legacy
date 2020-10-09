<?php


class MasterSourceModule extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_MASTER_SOURCE, $guestStatus);
    }
}