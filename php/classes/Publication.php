<?php


class Publication extends OpalProject
{
    public function getPublications() {
        return $this->opalDB->getPublications();
    }
}