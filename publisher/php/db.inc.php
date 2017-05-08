<?php

	$pathname 	= __DIR__;
	$abspath 	= str_replace('publisher/php', '', $pathname);

	include_once($abspath . 'php/config.php');

    try{
       $pdo = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       $pdo->exec('SET NAMES "utf8"');
   }catch(PDOException $e)
   {
       echo $e;
   }
?>
