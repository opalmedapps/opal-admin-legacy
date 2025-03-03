<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/*
 * This class is used as a catch all when a specific database is not connected. Instead of programming each method,
 * it catches all methods and returns an empty array.
 * */
class DatabaseDisconnected
{
    public function __call($name, $arguments) {
        return array();
    }

    public static function __callStatic($name, $arguments) {
        return array();
    }
}