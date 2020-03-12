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
    define( "ARIA_DB_DSN", "odbc:DRIVER=FreeTDS;SERVER=" . ARIA_DB_HOST . ";PORT=" . ARIA_DB_PORT . ";DATABASE=" . ARIA_DB_NAME);
define( "ARIA_DB_USERNAME", $config['databaseConfig']['aria']['username'] );
define( "ARIA_DB_PASSWORD", $config['databaseConfig']['aria']['password'] );

//	act.ActivityRevCount,
define("ARIA_GET_ALIASES_QT", "
SELECT DISTINCT
	act.ActivitySer AS ID,
	act.ActivityCode AS code,
	vva.Expression1 AS expression,
	Scheduled.type,
	act.ObjectStatus AS status,
	act.HstryDateTime AS lastUpdated
FROM
	variansystem.dbo.vv_Activity vva
	INNER JOIN variansystem.dbo.Activity act ON act.ActivityCode = vva.LookupValue
	INNER JOIN variansystem.dbo.ActivityCategory ON ActivityCategory.ActivityCategorySer = act.ActivityCategorySer
		AND ActivityCategory.DepartmentSer = vva.SubSelector
	INNER JOIN variansystem.dbo.ActivityInstance ai ON ai.ActivitySer = act.ActivitySer
	INNER JOIN (
		SELECT
			'2' AS type,
			sa.CreationDate,
			sa.ActivityInstanceSer,
			sa.ObjectStatus
		FROM
			variansystem.dbo.ScheduledActivity sa
		UNION
		SELECT
			'1' AS type,
			nsa.CreationDate,
			nsa.ActivityInstanceSer,
			nsa.ObjectStatus
		FROM variansystem.dbo.NonScheduledActivity nsa
	) AS Scheduled ON Scheduled.ActivityInstanceSer = ai.ActivityInstanceSer
		AND Scheduled.CreationDate >= '2018-01-01'
		AND Scheduled.ObjectStatus = 'Active'
ORDER BY
	vva.Expression1
");

define("ARIA_GET_ALIASES_DOC", "
SELECT DISTINCT
	nt.note_typ AS ID,
			'3' AS type,
	nt.note_typ_desc AS Name,
	nt.trans_log_tstamp AS CreationTimestamp,
	nt.trans_log_mtstamp AS ModifiedTimestamp
FROM
	varianenm.dbo.note_typ nt
	INNER JOIN varianenm.dbo.visit_note vn ON vn.note_typ = nt.note_typ
		AND vn.valid_entry_ind = 'Y'
ORDER BY note_typ_desc;
");