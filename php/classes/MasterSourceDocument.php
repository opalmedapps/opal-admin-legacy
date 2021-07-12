<?php


class MasterSourceDocument extends MasterSourceAlias {
    function getSourceDocumentDetails($post) {
        $this->checkReadAccess($post);
        return $this->_getSourceAliasDetails($post, ALIAS_TYPE_DOCUMENT);
    }
    
    public function insertSourceDocuments($post) {
        $this->checkWriteAccess($post);
        return $this->_insertSourceAliases($post, ALIAS_TYPE_DOCUMENT);
    }

    public function updateSourceDocuments($post) {
        $this->checkWriteAccess($post);
        return $this->_updateSourceAliases($post, ALIAS_TYPE_DOCUMENT);
    }

    public function doesDocumentExists($post) {
        $this->checkReadAccess($post);
        return $this->_doesAliasExists($post, ALIAS_TYPE_DOCUMENT);
    }

    function markAsDeletedSourceDocuments($post) {
        $this->checkDeleteAccess($post);
        return $this->_markAsDeletedSourceAliases($post, ALIAS_TYPE_DOCUMENT);
    }
}