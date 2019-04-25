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
        catch(PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Connection to the database failed.\r\nError : ". $e->getMessage());
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
                    if(isset($value["data_type"]) &&  $value["data_type"] != "")
                        $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                    else
                        $stmt->bindParam($value["parameter"], $value["variable"], self::getTypeOf($value["variable"]));
                }
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Fetch all failed.\r\nError : ". $e->getMessage());
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
                    if(isset($value["data_type"]) &&  $value["data_type"] != "")
                        $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                    else
                        $stmt->bindParam($value["parameter"], $value["variable"], self::getTypeOf($value["variable"]));
                }
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Fetch failed.\r\nError : ". $e->getMessage());
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
                    if(isset($value["data_type"]) &&  $value["data_type"] != "")
                        $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                    else
                        $stmt->bindParam($value["parameter"], $value["variable"], self::getTypeOf($value["variable"]));
                }
            }
            $stmt->execute();
            return true;
        }
        catch(PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Execution failed.\r\nError : ". $e->getMessage());
        }
    }

    /*
     * Destructor. Kills the connection
     * */
    public function disconnect() {
        $this->connection = null;
    }

    protected static function getTypeOf($aVar) {
        switch(gettype($aVar)) {
            case "integer":
                return PDO::PARAM_INT;
                break;
            case "boolean":
                return PDO::PARAM_BOOL;
                break;
            default:
                return PDO::PARAM_STR;
        }
    }

    /* Execute a query insert SQL command
     * Entry:   SQL INSERT command (String)
     * Exit:    ID of last entry
     */
    protected function queryInsert($sqlInsert, $paramList = array()) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->connection->prepare($sqlInsert);
            if(count($paramList) > 0) {
                foreach($paramList as $value) {
                    if(isset($value["data_type"]) &&  $value["data_type"] != "")
                        $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                    else
                        $stmt->bindParam($value["parameter"], $value["variable"], self::getTypeOf($value["variable"]));
                }
            }
            $stmt->execute();
            return $this->connection->lastInsertId();
        }
        catch(PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Insert query failed. $sqlInsert\r\nError : ". $e->getMessage());
        }

    }

    /*
     * This function build a SQL insert query with a table name and a list of records and launch its execution.
     * @param   table name where to insert (string)
     *          array of records that contain arrays of data to insert and their field name. Each array must have the
     *          same structure and same order.
     *          example:    Array (
	 *                          Array (
	 * 	                            "field1" => "data"
     *                              "field2" => "more data"
     *                              "field3" => "even more data"
     *                          )
	 *                          Array (
	 *                              "field1" => "enough data?"
     *                              "field2" => "no more data!"
     *                              "field3" => "data!"
	 *                          )
     *                      )
     * */
    protected function insertMultipleRecordsIntoTable($tableName, $records) {
        $tableName = strip_tags($tableName);
        $sqlInsert = "INSERT INTO $tableName (%%FIELDS%%) VALUES ";
        $multiples = array();
        $cpt = 0;
        $ready = array();
        foreach ($records as $data) {
            $cpt++;
            $fields = array();
            $params = array();
            foreach($data as $key=>$value) {
                array_push($fields, strip_tags($key));
                array_push($params, ":".strip_tags($key).$cpt);
                array_push($ready, array("parameter"=>":".strip_tags($key).$cpt,"variable"=>strip_tags($value)));
            }
            $sqlFieldNames = "`".implode("`, `", $fields)."`";
            array_push($multiples, implode(", ", $params));
        }

        $sqlInsert = str_replace("%%FIELDS%%", $sqlFieldNames, $sqlInsert) . "(" . implode("), (", $multiples) . ");";
        return $this->queryInsert($sqlInsert, $ready);
    }

    /*
     * This function build a SQL insert query with a table name and one record and launch its execution.
     * @param   table name where to insert (string)
     *          array of records that contain arrays of data to insert and their field name. Each array must have the
     *          same structure and same order.
     *          example:    Array (
	 * 	                            "field1" => "data"
     *                              "field2" => "more data"
     *                              "field3" => "even more data"
     *                      )
     * */
    protected function insertRecordIntoTable($tableName, $record) {
        $tableName = strip_tags($tableName);
        $sqlInsert = "INSERT INTO $tableName (%%FIELDS%%) VALUES ";
        $multiples = array();
        $cpt = 1;
        $ready = array();
        $fields = array();
        $params = array();
        foreach($record as $key=>$value) {
            array_push($fields, strip_tags($key));
            array_push($params, ":".strip_tags($key).$cpt);
            array_push($ready, array("parameter"=>":".strip_tags($key).$cpt,"variable"=>strip_tags($value)));
        }
        $sqlFieldNames = "`".implode("`, `", $fields)."`";
        array_push($multiples, implode(", ", $params));

        $sqlInsert = str_replace("%%FIELDS%%", $sqlFieldNames, $sqlInsert) . "(" . implode("), (", $multiples) . ");";

//        print $sqlInsert . "\r\n";
//        print_r($ready);

        return $this->queryInsert($sqlInsert, $ready);
    }
}