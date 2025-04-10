<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Classes\DB;
use PDO;

require_once __DIR__ . "/../../../../vendor/autoload.php";

class DB {
    // Get database connection
    public static function getDBConnection(String $host, String $port, String $database, String  $username, String $password, String $usessl = '0', String $sslCa = ''): PDO
    {
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );

        // Set SSL cert for db conn if specified in env
        if($usessl == '1') {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        $db = new PDO(
            "mysql:host={$host};dbname={$database};port={$port}",
            $username,
            $password,
            $options
        );
        $db->query("SET CHARACTER SET utf8");
        return $db;
    }
}
