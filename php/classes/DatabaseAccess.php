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

    /*
     * This function establish connection with the database
     * @param   nothing
     * @return  nothing
     * */
    protected function connectTo() {
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

    /*
     * this function is used to fetch all results from a SQL query by binding parameters.
     * @param   SQL query that begins with "SELECT" (string)
     *          array of parameters to bind (optional) following PDO rules
     *          ex: array(
     *                  array(
     *                      "parameter"=>":example",
     *                      "variable"=>"Hello world!",
     *                      "data_type"=>PDO::PARAM_STR,
     *                  )
     *              )
     * @return  array of result
     * */
    protected function fetchAll($sqlFetchAll, $paramList = array()) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->connection->prepare($sqlFetchAll);
            if(count($paramList) > 0) {
                foreach($paramList as $value) {
                    $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                }
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            echo "Fetch all $sqlFetchAll failed.\r\n$sqlFetchAll\r\nError : ". $e->getMessage();
            die();
        }
    }

    /*
     * this function is used to fetch a result from a SQL query by binding parameters.
     * @param   SQL query that begins with "SELECT" (string)
     *          array of parameters to bind (optional) following PDO rules
     *          ex: array(
     *                  array(
     *                      "parameter"=>":example",
     *                      "variable"=>"Hello world!",
     *                      "data_type"=>PDO::PARAM_STR,
     *                  )
     *              )
     * @return  array of result
     * */
    protected function fetch($sqlFetch, $paramList = array()) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->connection->prepare($sqlFetch);
            if(count($paramList) > 0) {
                foreach($paramList as $value) {
                    $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                }
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            echo "Fetch failed.\r\n$sqlFetch\r\nError : ". $e->getMessage();
            die();
        }
    }

    /*
     * this function execute a SQL query and returns true once completed.
     * @param   SQL query (string)
     *          array of parameters to bind (optional) following PDO rules
     *          ex: array(
     *                  array(
     *                      "parameter"=>":example",
     *                      "variable"=>"Hello world!",
     *                      "data_type"=>PDO::PARAM_STR,
     *                  )
     *              )
     * @return  after execution
     * */
    protected function execute($sqlQuery, $paramList = array()) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->connection->prepare($sqlQuery);
            if(count($paramList) > 0) {
                foreach($paramList as $value) {
                    $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                }
            }
            $stmt->execute();
            return true;
        }
        catch(PDOException $e) {
            echo "Query failed.\r\nError : " . $e->getMessage();
            die();
        }
    }

    /*
     * Destructor. Kills the connection
     * */
    public function disconnect() {
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
            echo "Insert query failed.\r\nError : " . $e->getMessage();
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