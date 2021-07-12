<?php


class MasterSourceAppointment extends MasterSourceAlias {
    function getSourceAppointmentDetails($post) {
        $this->checkReadAccess($post);
        return $this->_getSourceAliasDetails($post, ALIAS_TYPE_APPOINTMENT);
    }

    public function insertSourceAppointments($post) {
        $this->checkWriteAccess($post);
        return $this->_insertSourceAliases($post, ALIAS_TYPE_APPOINTMENT);
    }

    public function updateSourceAppointments($post) {
        $this->checkWriteAccess($post);
        return $this->_updateSourceAliases($post, ALIAS_TYPE_APPOINTMENT);
    }

    public function doesAppointmentExists($post) {
        $this->checkReadAccess($post);
        return $this->_doesAliasExists($post, ALIAS_TYPE_APPOINTMENT);
    }

    function markAsDeletedSourceAppointments($post) {
        $this->checkDeleteAccess($post);
        return $this->_markAsDeletedSourceAliases($post, ALIAS_TYPE_APPOINTMENT);
    }
}