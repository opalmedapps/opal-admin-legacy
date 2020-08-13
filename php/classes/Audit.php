<?php

class Audit extends Module
{
    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_AUDIT, $guestStatus);
    }

    /*
     * Get the list of audit. Because the front end does not support pagination or lazy loading, limit to the latest
     * 10,000 records.
     * @params  void
     * @return  array - latest entries in the audit table
     * */
    public function getAudit() {
        $this->checkReadAccess();
        return $this->opalDB->getAuditList();
    }

    /*
     * Get the details of a specific audit.
     * */
    public function getAuditDetails($auditId) {
        $this->checkReadAccess($auditId);
        return array();
    }
}