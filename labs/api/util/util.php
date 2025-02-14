<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Util;
use DateTime, DateTimeZone;
use Exception;
use Opal\Labs\Classes\Config;
require_once __DIR__ . "/../../../vendor/autoload.php";

Config::setEnvironment();

/**
 * @param string $dateTime
 * @return DateTime
 * @throws \Exception
 */
function get_date(string $dateTime="now"){
    return new DateTime($dateTime);
}
function format_date(DateTime $dateTime, ?string $dateFormat=NULL){
    if($dateFormat == null) $dateFormat = "Y-m-d H:i:s";
    return $dateTime->format($dateFormat);
}

/**
 * @param string $dateString
 * @param string|null $dateFormat
 * @return string
 * @throws \Exception
 */
function format_date_string(string $dateString, ?string $dateFormat=NULL)
{
    $date = new DateTime($dateString);
    return format_date($date, $dateFormat);
}
function get_request_content()
{
    $params = array();
    if (!empty($_POST))  // coming from a Form by submit button
    {
        $params = $_POST;
    } elseif (
        array_key_exists('REQUEST_METHOD', $_SERVER) &&
        $_SERVER['REQUEST_METHOD'] == 'POST'
    ) // // Coming from Interface Engine or from the angular test program
    {
        $params = json_decode(file_get_contents('php://input'), true) ?? array();
    }
    // The params may be sent with a single space if they are empty.
    // Set these empty params to NULL
    foreach ($params as $param) {
        if (!is_array($param) && ctype_space($param)) $param = NULL;
    }
    return $params;
}

/**
 * @param string $name
 * @return array|string
 * @throws Exception
 */
function get_env_var(string $name): string{
    $val = $_ENV[$name];
    if(!$val){
        throw new Exception("Environment variable: $name, not currently defined.");
    }
    return $val;
}

/**
 * Send Email to parties
 * @param string $message
 * @throws Exception
 */
function send_email(string $message){
    $message = $message. PHP_EOL. "ENV: ".$_ENV["ENV"];
    $headers = "From: <opal@muhc.mcgill.ca>\r\n";
    $headers .= "Organization: Opal Labs Interface\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-Mailer: PHP". phpversion() ."\r\n".
    $to = get_env_var("LABS_EMAIL_RECIPIENTS");
    $subject = "Error in Opal Labs Interface";
    @mail($to, $subject, $message,  $headers);
}
