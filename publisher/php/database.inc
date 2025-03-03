<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

	$pathname 	= __DIR__;
	$abspath 	= str_replace('publisher' . DIRECTORY_SEPARATOR . 'php', '', $pathname);

	include_once($abspath . 'php' . DIRECTORY_SEPARATOR . 'config.php');

    $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");

    if (USE_SSL) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = SSL_CA;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    try{
       $pdo = new PDO(
                OPAL_DB_DSN,
                OPAL_DB_USERNAME,
                OPAL_DB_PASSWORD,
                $options,
            );
       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       $pdo->exec('SET NAMES "utf8"');
   }catch(PDOException $e)
   {
       echo $e;
   }
?>
