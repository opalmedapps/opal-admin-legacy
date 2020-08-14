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
    public function getAudits() {
        $this->checkReadAccess();
        return $this->opalDB->getAudits();
    }

    /*
     * Get the details of a specific audit.
     * @params  $auditId : int - ID of the audit to get the dtails
     * @return  array - details of the audit.
     * */
    public function getAuditDetails($auditId) {
        $this->checkReadAccess($auditId);
        $result = $this->opalDB->getAuditDetails($auditId);
        if(count($result) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid audit ID.");
        return $result[0];
    }
}