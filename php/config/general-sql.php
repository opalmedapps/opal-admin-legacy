<?php
/**
 * User: Dominic Bourdua
 * Date: 4/12/2019
 * Time: 3:02 PM
 */

define("DEACTIVATE_FOREIGN_KEY_CONSTRAINT", "SET FOREIGN_KEY_CHECKS=0;");
define("ACTIVATE_FOREIGN_KEY_CONSTRAINT", "SET FOREIGN_KEY_CHECKS=1;");

define("SQL_GENERAL_INSERT_INTERSECTION_TABLE",
    "INSERT INTO %%TABLENAME%% (%%FIELDS%%) "
);

define("SQL_GENERAL_INSERT_INTO",
    SQL_GENERAL_INSERT_INTERSECTION_TABLE . "VALUES "
);

define("SQL_GENERAL_UNION_ALL"," UNION ALL ");

define("SQL_GENERAL_INSERT_INTERSECTION_TABLE_SUB_REQUEST",
    "SELECT %%VALUES%% FROM dual WHERE NOT EXISTS (SELECT %%FIELDS%% FROM %%TABLENAME%% tblnm WHERE %%CONDITIONS%%)"
);

define("SQL_GENERAL_UPDATE_RECORDS", "UPDATE %%TABLENAME%% SET %%NEWVALUES%% WHERE %%CONDITIONS%%");

$exceptionFields = array(":creationDate");