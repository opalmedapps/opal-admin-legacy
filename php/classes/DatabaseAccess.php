<?php
/**
 * This class stores all the database access and manipulations
 * User: Dominic Bourdua
 * Date: 4/10/2019
 * Time: 11:28 AM
 */

class DatabaseAccess extends HelpSetup
{
    protected $connection;
    protected $serverName;
    protected $port;
    protected $usernameDB;
    protected $password;
    protected $databaseName;
    protected $userId;
    protected $username;
    protected $isUserSetUp;

    /* constructor that connects to the database */
    function __construct($newServer = "localhost", $newDB = "", $newPort = "3306", $newUserDB = "root", $newPass = "", $newUserId = -1) {
        $this->serverName = $newServer;
        $this->port = $newPort;
        $this->usernameDB = $newUserDB;
        $this->password = $newPass;
        $this->databaseName = $newDB;
        $this->userId = $newUserId;
        $this->isUserSetUp = false;
        $this->connectTo();
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId) {
        $this->userId = $userId;
        if ($this->userId != -1 && $this->userId != "")
            $this->isUserSetUp = true;
    }

    /**
     * @return mixed
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    function connectTo() {
        try {
            $this->connection = new PDO(
                "mysql:host=$this->serverName;port=$this->port;dbname=$this->databaseName", $this->usernameDB, $this->password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            echo "Connection to $this->databaseName on $this->serverName failed.\r\nError : " . $e->getMessage();
            die();
        }
    }

    function fetchAll($sqlFetch) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->connection->prepare($sqlFetch);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            echo "Fetch all with query $sqlFetch failed.\r\nError : ". $e->getMessage();
            die();
        }
    }

    function fetch($sqlFetch) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->connection->prepare($sqlFetch);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            echo "Fetch with query $sqlFetch failed.\r\nError : ". $e->getMessage();
            die();
        }
    }

    function query($sqlQuery) {
        try {
            $this->connection->query($sqlQuery);
            return true;
        }
        catch(PDOException $e) {
            echo "Query $sqlQuery failed.\r\nError : " . $e->getMessage();
            die();
        }
    }

    function disconnect() {
        $this->connection = null;
    }

    /* Execute a query insert SQL command
     * Entry:   SQL INSERT command (String)
     * Exit:    ID of last entry
     */
    function queryInsert($sqlInsert) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->exec($sqlInsert);
            return $this->connection->lastInsertId();
        }
        catch(PDOException $e) {
            echo "Insert query $sqlInsert failed.\r\nError : " . $e->getMessage();
            die();
        }

    }

    /*
     * This function add a single line to a specified table.
     * Entry:   string for tableName
     *          array("field1"=>"value1", "field2"=>"value2", ... );
     * return:  ID of last insert.
     */
    function insertTableLine($tableName, $data) {
        $fields = array();
        $values = array();
        foreach ($data as $key => $value) {
            array_push($fields, $key);
            array_push($values, $value);
        }

        $sqlInsert = "INSERT INTO ".$tableName." (`".implode("`, `", $fields)."`) VALUES ('".implode("', '", $values)."');";
        return $this->queryInsert($sqlInsert);
    }
}