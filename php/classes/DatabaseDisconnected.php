<?php

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