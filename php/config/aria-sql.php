<?php

/**
 * Aria SQL config file that contains config access, table names and SQL queries used in opalDb
 * User: Dominic Bourdua
 * Date: 17/12/2019
 * Time: 11:21 AM
 * 
 */

// DEFINE ARIA SERVER/DATABASE CREDENTIALS HERE
// NOTE: This works for a MicrosoftSQL (MSSQL) setup.
define( "ARIA_DB_ENABLED", (intval($config['databaseConfig']['aria']['enabled']) == 0?false:true));
define( "ARIA_DB_HOST", $config['databaseConfig']['aria']['host'] );
define( "ARIA_DB_PORT", $config['databaseConfig']['aria']['port']);
define( "ARIA_DB_NAME", $config['databaseConfig']['aria']['name']);
if(in_array($_SERVER['REMOTE_ADDR'], $localHostAddr))
    define( "ARIA_DB_DSN", "odbc:Driver={SQL Server};Server=" . ARIA_DB_HOST);
else
    # define( "ARIA_DB_DSN", "dblib:host=" . ARIA_DB_HOST . ":" . ARIA_DB_PORT . "\\database" . ";charset=utf8");
    define( "ARIA_DB_DSN", "dblib:host=" . ARIA_DB_HOST . ":" . ARIA_DB_PORT . ";dbname=" . ARIA_DB_NAME . ";charset=utf8");
define( "ARIA_DB_USERNAME", $config['databaseConfig']['aria']['username'] );
define( "ARIA_DB_PASSWORD", $config['databaseConfig']['aria']['password'] );