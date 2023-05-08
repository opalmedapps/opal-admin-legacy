<?php

	$pathname 	= __DIR__;
	$abspath 	= str_replace('publisher' . DIRECTORY_SEPARATOR . 'php', '', $pathname);

	$ignoreSecuredConnection = true;
	include_once($abspath . 'php' . DIRECTORY_SEPARATOR . 'config.php');

    try{
       $pdo = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, DB_OPTS);
       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       $pdo->exec('SET NAMES "utf8"');
   }catch(PDOException $e)
   {
       echo $e;
   }
?>
