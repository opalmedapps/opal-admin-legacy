<?php

/*
 * Post object structure
 * */

class Post extends OpalProject {

    /*
     * This function returns the list of available posts for opalAdmin.
     * TODO add lazy loading with pagination
     * @params void
     * @return  array of posts
     * */
    public function getPosts() {
        return $this->opalDB->getPosts();
    }

    /*
     * Static function to validate and sanitize a post to insert
     * @params  array for future post to insert
     * @return  array of sanitized data or false if incorrect/incomplete
     * */
    static function validateAndSanitize($postToSanitize) {
        $validatedPost = array(
            "PostName_EN"=>strip_tags($postToSanitize['name_EN']),
            "PostName_FR"=>strip_tags($postToSanitize['name_FR']),
            "PostType"=>strip_tags($postToSanitize['type']),
            "Body_EN"=>filter_var($_POST['body_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
            "Body_FR"=>filter_var($_POST['body_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
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
    public function getPostDetails ($postId) {
        return $this->opalDB->getPostDetails($postId);
    }

    /*
     * This function inserts a post into the opalDB
     *
     * @param array that contains a line for the post table
     * @return ID of the new entry
     * */
    public function insertPost( $toInsert ) {
        $toInsert["PublishDate"]="0000-00-00 00:00:00";
        return $this->opalDB->insertPost($toInsert);
    }

    /*
     * Get the chart logs of a specific post
     * @param   $post (array) $_POST that contains serial and type
     * @return  $data (array) array of chart log results.
     * */
    public function getPostChartLogs ($post) {
        $data = array();
        $serial = strip_tags($post["serial"]);
        $type = strip_tags($post["type"]);

        if($serial == "" || $type == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid settings for chart log.");

        if ($type == 'Announcement')
            $result = $this->opalDB->getAnnouncementChartLogs($serial);
        else if ($type == 'Treatment Team Message')
            $result = $this->opalDB->getTTMChartLogs($serial);
        else if ($type == 'Patients for Patients')
            $result = $this->opalDB->getPFPChartLogs($serial);
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Unknown type of post.");

        //The Y value has to be converted to an int, or the chart log will reject it on the front end.
        foreach ($result as &$item) {
            $item["y"] = intval($item["y"]);
        }

        if (count($result) > 0)
            array_push($data, array("name"=>$type, "data"=>$result));

        return $data;
    }

    /*
     * Returns all the chart logs from a list of post IDs depending if it is announcement, treatment team message or
     * patients for patients.
     * @params  $serials (array) list of serials to look for
     *          $type (string) type of post
     * @return  array of chrat log value
     * */
    public function getPostListLogs($serials, $type) {
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
        $currentPost = $this->opalDB->getPostDetails($postId);
        if($currentPost["locked"] == 0)
            return $this->opalDB->markAsDeleted(OPAL_POST_TABLE, OPAL_POST_PK, $postId);
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Post locked.");
    }

    /*
     * Updates a post's details if it was not locked before (means published) or deleted.
     * @params  array $postDetails - contains post details
     * @returns int number of record affected OR false if a problem occurs
     * */
    public function updatePost($postDetails) {
        $currentPost = $this->opalDB->getPostDetails($postDetails["PostControlSerNum"]);
        if($currentPost["locked"] == 0)
            return $this->opalDB->updatePost($postDetails);
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Post locked.");
    }
}