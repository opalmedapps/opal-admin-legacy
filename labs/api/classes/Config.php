<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Classes;

require_once __DIR__ . "/../../../vendor/autoload.php";

use Dotenv\Dotenv;
use Exception;

class Config
{
    static function setEnvironment()
    {
        try {
            $dot_env = Dotenv::createImmutable(__DIR__, "/../../.env");
            // don't require the .env to be present so that only env variables are sufficient
            $dot_env->safeload();

            // change default timezone if the TZ env variable is defined
            // to be consistent
            if (isset($_ENV["TZ"])) {
                date_default_timezone_set($_ENV["TZ"]);
            }
        } catch (Exception $ex) {
            echo "Failed to load environment variable file!\n";
        }
    }
}
