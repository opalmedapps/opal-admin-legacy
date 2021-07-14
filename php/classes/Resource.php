<?php


class Resource extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_RESOURCE, $guestStatus);
    }

    public function insertResource($post) {
        $this->checkWriteAccess();
    }
}