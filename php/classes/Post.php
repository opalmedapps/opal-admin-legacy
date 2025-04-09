<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/*
 * Post object structure
 * */

class Post extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_POST, $guestStatus);
    }

    /*
     * This function returns the list of available posts for opalAdmin.
     * TODO add lazy loading with pagination
     * @params void
     * @return  array of posts
     * */
    public function getPosts() {
        $this->checkReadAccess();
        return $this->opalDB->getPosts();
    }

    /*
     * Static function to validate and sanitize a post to insert
     * @params  array for future post to insert
     * @return  array of sanitized data or false if incorrect/incomplete
     * */
    protected function _validateAndSanitize($postToSanitize) {
        $validatedPost = array(
            "PostName_EN"=>strip_tags($postToSanitize['name_EN']),
            "PostName_FR"=>strip_tags($postToSanitize['name_FR']),
            "PostType"=>strip_tags($postToSanitize['type']),
            "Body_EN"=>filter_var($_POST['body_EN'], FILTER_SANITIZE_ADD_SLASHES),
            "Body_FR"=>filter_var($_POST['body_FR'], FILTER_SANITIZE_ADD_SLASHES),
        );

        if($postToSanitize["serial"] != "") {
            $validatedPost["PostControlSerNum"] = strip_tags($postToSanitize["serial"]);
            if($validatedPost["PostControlSerNum"] == "")
                return false;
        }

        if ($validatedPost["PostName_EN"] == "" || $validatedPost["PostName_FR"] == "" || $validatedPost["PostType"] == "" || $validatedPost["Body_EN"] == "" || $validatedPost["Body_FR"] == "")
            return false;

        return $validatedPost;
    }

    /*
     * Gets details on a particular post
     * @param integer $postSer : the post serial number
     * @return array $postDetails : the post details
     */
    public function getPostDetails($postId) {
        $this->checkReadAccess($postId);
        $results = $this->opalDB->getPostDetails($postId);
        $results["body_EN"] = stripslashes($results["body_EN"]);
        $results["body_FR"] = stripslashes($results["body_FR"]);
        return $results;
    }

    /*
     * This function inserts a post into the opalDB
     *
     * @param array that contains a line for the post table
     * @return ID of the new entry
     * */
    public function insertPost($post) {
        $this->checkWriteAccess($post);

        $sanitizedPost = $this->_validateAndSanitize($post);
        if(!$sanitizedPost)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid post format");

        $sanitizedPost["PublishDate"]="0000-00-00 00:00:00";
        return $this->opalDB->insertPost($sanitizedPost);
    }

    /*
     * Returns all the chart list from a list of cron IDs depending if it is announcement, treatment team message or
     * patients for patients.
     * @params  $serials (array) list of serials to look for
     *          $type (string) type of post
     * @return  array of chrat log value
     * */
    public function getPostListLogs($serials, $type) {
        $this->checkReadAccess(array($serials, $type));
        if ($type == 'Announcement')
            return  $this->opalDB->getAnnouncementChartLogsByIds($serials);
        else if ($type == "Treatment Team Message")
            return $this->opalDB->getTTMChartLogsByIds($serials);
        else if ($type == "Patients for Patients")
            return $this->opalDB->getPFPChartLogsByIds($serials);
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Unknown type of post.");
    }

    /*
     * Get the chart logs of a specific post
     * @param   $serial (int) SerNum of the Post
     *          $type (string) type of post
     * @return  $data (array) array of chart log results.
     * */
    public function getPostChartLogs($serial, $type) {
        $this->checkReadAccess(array($serial, $type));
        $data = array();
        if($serial == "" || $type == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid settings for chart log.");
        $result = $this->opalDB->getPublicationChartLogs(MODULE_POST, $serial);

        //The Y value has to be converted to an int, or the chart log will reject it on the front end.
        $tempArray = array();
        foreach($result as $item)
            $item["y"] = intval($item["y"]);
            array_push($tempArray, $item);
        $result = $tempArray;

        if (count($result) > 0)
            array_push($data, array("name"=>$type, "data"=>$result));

        return $data;
    }

    /*
     * Marks a post as deleted if the post was not locked (means published) before.
     *
     * WARNING!!! No record should be EVER be removed from the opalDB database! It should only being marked as
     * being deleted ONLY after it was verified the record is not locked. Not following the proper procedure will
     * have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @params  int postId - ID of the post to delete
     * @returns int number of record affected OR false if a problem occurs
     * */
    public function deletePost($postId) {
        $this->checkDeleteAccess($postId);
        $currentPost = $this->opalDB->getPostDetails($postId);
        if($currentPost["locked"] == 0)
            return $this->opalDB->markPostAsDeleted(OPAL_POST_TABLE, OPAL_POST_PK, $postId);
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Post locked.");
    }

    /*
     * Updates a post's details if it was not locked before (means published) or deleted.
     * @params  array $postDetails - contains post details
     * @returns int number of record affected OR false if a problem occurs
     * */
    public function updatePost($post) {
        $this->checkWriteAccess($post);

        $sanitizedPost = $this->_validateAndSanitize($post);
        if(!$sanitizedPost)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid post format");

        $currentPost = $this->opalDB->getPostDetails($sanitizedPost["PostControlSerNum"]);
        if($currentPost["locked"] == 0)
            return $this->opalDB->updatePost($sanitizedPost);
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Post locked.");
    }
}
