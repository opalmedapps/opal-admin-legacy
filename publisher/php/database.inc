<?php

	$pathname 	= __DIR__;
	$abspath 	= str_replace('publisher' . DIRECTORY_SEPARATOR . 'php', '', $pathname);

	$ignoreSecuredConnection = true;
	include_once($abspath . 'php' . DIRECTORY_SEPARATOR . 'config.php');

    try{
       $pdo = new PDO(
                OPAL_DB_DSN,
                OPAL_DB_USERNAME,
                OPAL_DB_PASSWORD,
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    PDO::MYSQL_ATTR_SSL_CA => SSL_CA,
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
                )
            );
       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       $pdo->exec('SET NAMES "utf8"');
   }catch(PDOException $e)
   {
       echo $e;
   }
?>
