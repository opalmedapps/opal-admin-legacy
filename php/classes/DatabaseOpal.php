<?php
/**
 * Database Opal access class
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 1:00 PM
 */

class DatabaseOpal extends DatabaseAccess {

    function getUserInfo($userId) {
        $defaultUSer = array("userId"=>-1, "username"=>"PROBLEM_NO_USER_FOUND", "language"=>"en");
        if ($userId == "")
            return $defaultUSer;
        $sqlFetchUser = "SELECT OAUserSerNum AS userId, Username AS username, Language as language FROM oauser WHERE OAUserSerNum = :userId";
        try {
            $stmt = $this->connection->prepare($sqlFetchUser);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) <=0) {
                return $defaultUSer;
            }
            else
                return $result[0];
        }
        catch(PDOException $e) {
            echo "Fetching user info with query $sqlFetchUser failed.\r\nError : ". $e->getMessage();
            die();
        }
    }

    function getLockedQuestionnaires($questionnairesList) {
        $sql = "SELECT COUNT(*) AS total FROM questionnairecontrol WHERE QuestionnaireDBSerNum IN ( :questionnaireList )";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':questionnaireList', $questionnairesList, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        }
        catch(PDOException $e) {
            echo "Fetching locked questionnaires failed.\r\nError : ". $e->getMessage();
            die();
        }
    }
}