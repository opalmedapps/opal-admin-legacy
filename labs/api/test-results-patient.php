<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/util/util.php";

use Opal\Labs\Classes\DB\PatientDB;
use Opal\Labs\Classes\Validator;
use Opal\Labs\Classes\TestResultProcessor;

use function Opal\Labs\Util\get_request_content;

ini_set('max_execution_time', 3600);
header('Content-type: application/json');

$params = get_request_content();
// Check request parameters for existence
$params = validate_parameters($params);

// Throws 500 if DB is down.
$processor = TestResultProcessor::create();

try {
    try {
        $patient = PatientDB::create()->getPatient($params["PatientId"], $params["Site"]);
        $result = $processor->processPatient($patient,
            array_key_exists("fromDate", $params) ? new DateTime($params["fromDate"]) : NULL,
            array_key_exists("toDate", $params) ? new DateTime($params["toDate"]): NULL
        );
        if (!$result["success"]) {
            http_response_code(400);
            exit(json_encode(array(
                "status" => "error",
                "reason" =>  $result["error"]->getMessage()
            )));
        }
        exit(json_encode(array(
            "status" => "success",
            "results" => array("insertedRows" => $result["insertedRows"], "updatedRows" => $result["updatedRows"])
        )));
    } catch (Exception $ex) {
        http_response_code(400);
        exit(json_encode(array("status" => "error", "reason" => $ex->getMessage())));
    }

} catch (Exception $err) {
    http_response_code(400);
    exit(json_encode(array("status" => "error", "reason" => $err->getMessage())));
}

////////////////////////////////////////////////////////////

function validate_parameters($params)
{
    try {
        $validator = new Validator($params);
        if ((!isset($params["PatientId"]) || !isset($params["Site"]))) {
            throw new Exception("Both PatientId and Site parameters must be defined");
        }
        if (isset($params->fromDate)) $validator->toDate("fromDate");
        if (isset($params->toDate)) $validator->toDate("toDate");
    } catch (Exception $e) {
        http_response_code(400);
        exit(json_encode(array("status" => "error", "reason" => $e->getMessage())));
    }
    return $params;
}
