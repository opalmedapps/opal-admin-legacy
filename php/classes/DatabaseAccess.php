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
    protected $OAUserId;
    protected $username;
    protected $userRole;

    /* constructor that connects to the database */
    function __construct($newServer = "localhost", $newDB = "", $newPort = "3306", $newUserDB = "root", $newPass = "", $newUserId = false) {
        $this->serverName = $newServer;
        $this->port = $newPort;
        $this->usernameDB = $newUserDB;
        $this->password = $newPass;
        $this->databaseName = $newDB;
        $this->_connectTo();
    }

    /*
     * Destructor. Kills the connection
     * */
    public function disconnect() {
        $this->connection = null;
    }

    /**
     * @return mixed
     */
    public function getOAUserId()
    {
        return $this->OAUserId;
    }

    /**
     * @param mixed $OAUserId
     */
    public function setOAUserId($OAUserId)
    {
        $this->OAUserId = $OAUserId;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUserRole()
    {
        return $this->userRole;
    }

    /**
     * @param mixed $userRole
     */
    public function setUserRole($userRole)
    {
        $this->userRole = $userRole;
    }

    /*
     * This function establish connection with the database
     * @param   nothing
     * @return  nothing
     * */
    protected function _connectTo() {
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
    protected function _fetchAll($sqlFetchAll, $paramList = array()) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->connection->prepare($sqlFetchAll);
            if(count($paramList) > 0) {
                foreach($paramList as $value) {
                    if(isset($value["data_type"]) &&  $value["data_type"] != "")
                        $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                    else
                        $stmt->bindParam($value["parameter"], $value["variable"], self::_getTypeOf($value["variable"]));
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
    protected function _fetch($sqlFetch, $paramList = array()) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->connection->prepare($sqlFetch);
            if(count($paramList) > 0) {
                foreach($paramList as $value) {
                    if(isset($value["data_type"]) &&  $value["data_type"] != "")
                        $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                    else
                        $stmt->bindParam($value["parameter"], $value["variable"], self::_getTypeOf($value["variable"]));
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
    protected function _execute($sqlQuery, $paramList = array()) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->connection->prepare($sqlQuery);
            if(count($paramList) > 0) {
                foreach($paramList as $value) {
                    if(isset($value["data_type"]) &&  $value["data_type"] != "")
                        $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                    else
                        $stmt->bindParam($value["parameter"], $value["variable"], self::_getTypeOf($value["variable"]));
                }
            }
            $stmt->execute();
            return $stmt->rowCount();
        }
        catch(PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Execution failed.\r\n$sqlQuery\r\nError : ". $e->getMessage());
        }
    }

    protected static function _getTypeOf($aVar) {
        if(filter_var($aVar, FILTER_VALIDATE_INT) !== false)
            return PDO::PARAM_INT;
        else if (filter_var($aVar, FILTER_VALIDATE_BOOLEAN) !== false)
            return PDO::PARAM_BOOL;
        else
            return PDO::PARAM_STR;
    }

    /* Execute a query insert SQL command
     * Entry:   SQL INSERT command (String)
     * Exit:    ID of last entry
     */
    protected function _queryInsert($sqlInsert, $paramList = array()) {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $this->connection->prepare($sqlInsert);
            if(count($paramList) > 0) {
                foreach($paramList as $value) {
                    if(isset($value["data_type"]) &&  $value["data_type"] != "")
                        $stmt->bindParam($value["parameter"], $value["variable"], $value["data_type"]);
                    else
                        $stmt->bindParam($value["parameter"], $value["variable"], self::_getTypeOf($value["variable"]));
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
    protected function _insertMultipleRecordsIntoTable($tableName, $records) {
        $sqlInsert = str_replace("%%TABLENAME%%", strip_tags($tableName), SQL_GENERAL_INSERT_INTO);
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
        return $this->_queryInsert($sqlInsert, $ready);
    }

    protected function _insertMultipleRecordsIntoTableConditional($tableName, $records) {
        $sqlSubSet = array();
        $cpt = 0;
        $params = array();

        foreach ($records as $record) {
            $cpt++;
            $fieldsName = array();
            $subFieldsName = array();
            $ids = array();
            $conditions = array();
            foreach($record as $key=>$value) {
                array_push($fieldsName, "`$key`");
                array_push($subFieldsName, "tblnm.$key");
                array_push($ids, $value);
                array_push($conditions, "tblnm.$key = :".$key.$cpt);
                array_push($params, array("parameter"=>":".$key.$cpt, "variable"=>$value));
            }
            $subSql = str_replace("%%VALUES%%", implode(", ", $ids), SQL_GENERAL_INSERT_INTERSECTION_TABLE_SUB_REQUEST);
            $subSql = str_replace("%%FIELDS%%", implode(", ", $subFieldsName), $subSql);
            $subSql = str_replace("%%CONDITIONS%%", implode(" AND ", $conditions), $subSql);
            array_push($sqlSubSet, $subSql);
        }

        $finalSql =
            str_replace("%%TABLENAME%%", $tableName, str_replace("%%FIELDS%%", implode(",", $fieldsName), SQL_GENERAL_INSERT_INTERSECTION_TABLE)
                . implode(SQL_GENERAL_UNION_ALL, $sqlSubSet));

        return $this->_execute($finalSql, $params);
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
    protected function _insertRecordIntoTable($tableName, $record) {
        $sqlInsert = str_replace("%%TABLENAME%%", strip_tags($tableName), SQL_GENERAL_INSERT_INTO);
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
        return $this->_queryInsert($sqlInsert, $ready);
    }


    protected function _updateRecordIntoTable($sqlQuery, $record) {
        $ready = array();
        foreach($record as $key=>$value) {
            array_push($ready, array("parameter"=>":".strip_tags($key),"variable"=>strip_tags($value)));
        }

        return $this->_execute($sqlQuery, $ready);
    }
}