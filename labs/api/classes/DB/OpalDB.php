<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Classes\DB;
require_once __DIR__ . "/../../../vendor/autoload.php";

use Exception;
use Opal\Labs\Classes\Config;
use Opal\Labs\Classes\Validator;
use PDO;

Config::setEnvironment();

class OpalDB
{
    private static PDO $instance;

    /**
     * @return PDO Connection to OpalDB database
     * @throws Exception
     */
    public static function getConnection(): PDO
    {
        if(!isset(OpalDB::$instance)){
            $validator = new Validator($_ENV);
            $validator->required(["OPAL_DB_HOST", "OPAL_DB_PORT", "OPAL_DB_PASSWORD", "OPAL_DB_USER", "DATABASE_USE_SSL", "SSL_CA"]);
            OpalDB::$instance = DB::getDBConnection(
                $_ENV['OPAL_DB_HOST'],
                $_ENV['OPAL_DB_PORT'],
                'OpalDB',
                $_ENV['OPAL_DB_USER'],
                $_ENV['OPAL_DB_PASSWORD'],
                $_ENV['DATABASE_USE_SSL'],
                $_ENV['SSL_CA'],
            );
        }
        return OpalDB::$instance;
    }
}
