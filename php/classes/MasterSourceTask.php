<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

class MasterSourceTask extends MasterSourceAlias {

    function getSourceTaskDetails($post) {
        $this->checkReadAccess($post);
        return $this->_getSourceAliasDetails($post, ALIAS_TYPE_TASK);
    }

    public function insertSourceTasks($post) {
        $this->checkWriteAccess($post);
        return $this->_insertSourceAliases($post, ALIAS_TYPE_TASK);
    }

    public function updateSourceTasks($post) {
        $this->checkWriteAccess($post);
        return $this->_updateSourceAliases($post, ALIAS_TYPE_TASK);
    }

    public function doesTaskExists($post) {
        $this->checkReadAccess($post);
        return $this->_doesAliasExists($post, ALIAS_TYPE_TASK);
    }

    function markAsDeletedSourceTasks($post) {
        $this->checkDeleteAccess($post);
        return $this->_markAsDeletedSourceAliases($post, ALIAS_TYPE_TASK);
    }
}