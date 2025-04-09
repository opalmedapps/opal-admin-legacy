<?php

// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Opal\Labs;

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__."/util/util.php";

use DateTime;
use Exception;
use PDO;
use Redis;
use GuzzleHttp\Client;
use Opal\Labs\Classes\DB\PatientDB;
use Opal\Labs\Classes\Patient;
use Opal\Labs\Classes\DB\OpalDB;
use Opal\Labs\Classes\Validator;

use function Opal\Labs\Util\get_env_var;
use function Opal\Labs\Util\get_request_content;
use function Opal\Labs\Util\send_email;

header("Content-type: application/json");

$params = get_request_content();
$params = validateParameters($params);

try {
    $patient = PatientDB::create()->getPatient($params["mrn"], $params["site"]);
    $opalDB = OpalDB::getConnection();
    $backendPatient = getBackendPatient($patient);

    processLabResult(
        $opalDB,
        $patient,
        $backendPatient,
        $params["labOrders"]
    );
}
catch(Exception $e) {
    // send_email("Unable to process lab: \n".print_r($params, true)."\n Error: \n".$e->getMessage());
    http_response_code(400);
    exit(json_encode(array("status" => "error", "reason" => $e->getMessage())));
}

exit(json_encode(array("status" => "success")));

////////////////////////////////////////////////////////////

function validateParameters($params)
{
    try {
        $validator = new Validator($params);
        $validator->required([
            "mrn",
            "site",
            "labOrders"
        ]);

        foreach($params["labOrders"] as &$order) {
            $orderValidator = new Validator($order);
            $orderValidator->required([
                "collectedDatetime",
                "labId",
                "testComponents",
                "testGroupCode",
                "testGroupDescription"
            ]);

            // TODO: ensure that datetimes have the correct timezone
            $order["collectedDatetime"]  = new DateTime($order["collectedDatetime"]);

            foreach($order["testComponents"] as &$lab) {
                $labValidator = new Validator($lab);
                $labValidator->required([
                    "abnormalFlag",
                    "maxRange",
                    "minRange",
                    "resultDatetime",
                    "testCode",
                    "testCodeDescription",
                    "testValue",
                    "unitDescription",
                ]);

                // TODO: ensure that datetimes have the correct timezone
                $lab["resultDatetime"] = new DateTime($lab["resultDatetime"]);
            }
        }
    }
    catch (Exception $e) {
        http_response_code(400);
        exit(json_encode(array("status" => "error", "reason" => $e->getMessage())));
    }

    return $params;
}

function processLabResult(
    PDO $dbh,
    Patient $patient,
    object $backendPatient,
    array $labOrders
): void
{
    // maintain mapping of test code to test control
    $testControls = array();
    // keep copy of labs with their availableAt datetime
    $labs = array();

    foreach($labOrders as $order) {
        //insert or update lab result codes
        updateTestGroup($dbh, $order["testGroupCode"], $order["testGroupDescription"]);

        foreach($order["testComponents"] as $lab) {
            $testCode = $lab["testCode"];
            updateTestComponent($dbh, $testCode, $lab["testCodeDescription"]);

            $testControl = getTestControl($dbh, $testCode);
            $testControls[$testCode] = $testControl;

            // copy the Datetime object to only modify the copy
            $availableAt = clone $lab["resultDatetime"];

            if (!$backendPatient->is_adult) {
                $daysToAdd = ($testControl["InterpretationRecommended"] === 1)
                ? $backendPatient->non_interpretable_lab_result_delay
                : $backendPatient->interpretable_lab_result_delay;
                $availableAt = $availableAt->modify("+{$daysToAdd} days");
            }

            $labs[] = $lab;
            $labs[count($labs) - 1]["availableAt"] = $availableAt;

            //insert the patient's lab result
            updateTestResult(
                dbh:                $dbh,
                patient:            $patient,
                testCode:           $testCode,
                testGroupCode:      $order["testGroupCode"],
                collectedDate:      $order["collectedDatetime"],
                resultDate:         $lab["resultDatetime"],
                testValue:          $lab["testValue"],
                unitDescription:    $lab["unitDescription"],
                minRange:           $lab["minRange"],
                maxRange:           $lab["maxRange"],
                abnormalFlag:       $lab["abnormalFlag"],
                availableAt:        $availableAt,
            );
        }
    }

    // Send the Opal patient a push notification if at least one lab result is not delayed
    foreach($labs as $lab) {
        $testControl = $testControls[$lab["testCode"]];
        $now = new DateTime();

        // the lab result has to be published and available (not a delayed lab result)
        if ($testControl["PublishFlag"] === 1 && $now >= $lab["availableAt"]) {
            // create a notification record for in-app notifications
            insertNotification($dbh, $patient, -1);
            sendPushNotification($backendPatient, $order["labId"]);
            // only send one push notification at a time
            break;
        }
    }

}

function updateTestGroup(PDO $dbh, string $testGroupCode, string $testGroupDescription): void
{
    //check if the group already exists
    //if it does, update it, otherwise insert a new record

    $groupQuery = $dbh->prepare("
        SELECT
            ExpressionName
        FROM
            TestGroupExpression
        WHERE
            TestCode = :testCode
    ");
    $groupQuery->execute([":testCode" => $testGroupCode]);

    $group = $groupQuery->fetchAll()[0] ?? NULL;

    if($group === NULL) {
        $dbh->prepare("
            INSERT INTO TestGroupExpression
                (TestCode, ExpressionName)
            VALUES
                (:testCode, :testName)
        ")->execute([
            ":testCode" => $testGroupCode,
            ":testName" => $testGroupDescription
        ]);
    }
}

function updateTestComponent(PDO $dbh, string $testCode, string $testCodeDescription): void
{
    //check if the component already exists
    //if it does, update it, otherwise insert a new record

    $componentQuery = $dbh->prepare("
        SELECT
            ExpressionName
        FROM
            TestExpression
        WHERE
            TestCode = :testCode
    ");
    $componentQuery->execute([":testCode" => $testCode]);

    $component = $componentQuery->fetchAll()[0] ?? NULL;

    if($component === NULL) {
        $dbh->prepare("
            INSERT INTO TestExpression
                (TestCode, ExpressionName)
            VALUES
                (:testCode, :testName)
        ")->execute([
            ":testCode" => $testCode,
            ":testName" => $testCodeDescription
        ]);
    }
}

function getTestControl(PDO $dbh, string $testCode): array
{
    $controlQuery = $dbh->prepare("
        SELECT
            tc.PublishFlag, tc.InterpretationRecommended
        FROM
            TestControl AS tc
        INNER JOIN
            TestExpression AS te
        ON
            tc.TestControlSerNum = te.TestControlSerNum
        WHERE
            te.TestCode = :testCode
    ");
    $controlQuery->execute([":testCode" => $testCode]);
    $control = $controlQuery->fetchAll()[0] ?? array(
        "PublishFlag" => 0,
        "InterpretationRecommended" => 0
    );

    return $control;
}

function updateTestResult(
    PDO $dbh,
    Patient $patient,
    string $testCode,
    string $testGroupCode,
    DateTime $collectedDate,
    DateTime $resultDate,
    string $testValue,
    string $unitDescription,
    string $minRange,
    string $maxRange,
    ?string $abnormalFlag,
    DateTime $availableAt
): void
{
    //perform some processing on the lab data
    $minNumeric = is_numeric($minRange) ? (float) $minRange : null;
    $maxNumeric = is_numeric($maxRange) ? (float) $maxRange : null;

    $range = "$minRange-$maxRange";

    $testValueNumeric = is_numeric($testValue) ? floatval($testValue) : null;

    //insert the lab into the db
    updateTestResultToDb(
        dbh:                $dbh,
        patient:            $patient,
        testCode:           $testCode,
        testGroupCode:      $testGroupCode,
        collectedDate:      $collectedDate,
        resultDate:         $resultDate,
        testValue:          $testValue,
        testValueNumeric:   $testValueNumeric,
        unitDescription:    $unitDescription,
        range:              $range,
        minNorm:            $minNumeric,
        maxNorm:            $maxNumeric,
        abnormalFlag:       $abnormalFlag,
        availableAt:        $availableAt,
    );
}

function updateTestResultToDb(
    PDO $dbh,
    Patient $patient,
    string $testCode,
    string $testGroupCode,
    DateTime $collectedDate,
    DateTime $resultDate,
    string $testValue,
    ?float $testValueNumeric,
    string $unitDescription,
    string $range,
    ?float $minNorm,
    ?float $maxNorm,
    ?string $abnormalFlag,
    DateTime $availableAt
): void
{
    // NOTE: This query is duplicated in TestResultProcessor.php
    $dbh->prepare("
        INSERT INTO PatientTestResult(
            PatientSerNum,
            TestExpressionSerNum,
            TestGroupExpressionSerNum,
            CollectedDateTime,
            TestValue,
            TestValueNumeric,
            ResultDateTime,
            UnitDescription,
            NormalRange,
            NormalRangeMin,
            NormalRangeMax,
            AbnormalFlag,
            DateAdded,
            AvailableAt
        )
        SELECT
            PH.PatientSerNum,
            TRE.TestExpressionSerNum,
            TGE.TestGroupExpressionSerNum,
            :testDate,
            :testValue,
            :testValueNumeric,
            :resultDate,
            :unitDescription,
            :normalRange,
            :minNorm,
            :maxNorm,
            :abnormalFlag,
            :dateAdded,
            :availableAt
        FROM
            Patient_Hospital_Identifier PH
            INNER JOIN TestExpression TRE ON TRE.TestCode = :testCode
            INNER JOIN TestGroupExpression TGE ON TGE.TestCode = :testGroupCode
        WHERE
            PH.MRN = :mrn
            AND PH.Hospital_Identifier_Type_Code = :site
            AND PH.Is_Active = 1
        ON DUPLICATE KEY UPDATE
            TestValue         = VALUES(TestValue),
            TestValueNumeric  = VALUES(TestValueNumeric),
            ResultDateTime    = VALUES(ResultDateTime),
            UnitDescription   = VALUES(UnitDescription),
            NormalRange       = VALUES(NormalRange),
            NormalRangeMin    = VALUES(NormalRangeMin),
            NormalRangeMax    = VALUES(NormalRangeMax),
            AbnormalFlag      = VALUES(AbnormalFlag),
            AvailableAt       = VALUES(AvailableAt)
    ")->execute([
        ":mrn"              => $patient->mrn,
        ":site"             => $patient->site,
        ":testCode"         => $testCode,
        ":testGroupCode"    => $testGroupCode,
        ":testDate"         => $collectedDate->format("Y-m-d H:i:s"),
        ":testValue"        => $testValue,
        ":testValueNumeric" => $testValueNumeric,
        ":resultDate"       => $resultDate->format("Y-m-d H:i:s"),
        ":unitDescription"  => $unitDescription,
        ":normalRange"      => $range,
        ":minNorm"          => $minNorm,
        ":maxNorm"          => $maxNorm,
        ":abnormalFlag"     => $abnormalFlag,
        ":dateAdded"        => (new DateTime())->format("Y-m-d H:i:s"),
        "availableAt"       => $availableAt->format("Y-m-d H:i:s")
    ]);
}

function insertNotification(
    PDO $dbh,
    Patient $patient,
    int $labRefID,
): void
{
    $dbh->prepare("
        INSERT INTO Notification(
            PatientSerNum,
            NotificationControlSerNum,
            RefTableRowSerNum,
            DateAdded,
            ReadStatus,
            RefTableRowTitle_EN,
            RefTableRowTitle_FR
        )
        SELECT
            PH.PatientSerNum,
            (
                SELECT
                    ntc.NotificationControlSerNum
                FROM NotificationControl ntc
                WHERE ntc.NotificationType = 'NewLabResult'
            ) AS NotificationControlSerNum,
            :labRefID,
            NOW(),
            0,
            'New Lab Result',
            'Nouveau résultat de laboratoire'
        FROM Patient_Hospital_Identifier PH
        WHERE
            PH.MRN = :mrn
            AND PH.Hospital_Identifier_Type_Code = :site
            AND PH.Is_Active = 1
    ")->execute([
        ":mrn"              => $patient->mrn,
        ":site"             => $patient->site,
        ":labRefID"         => $labRefID,
    ]);
}

function sendPushNotification(mixed $backendPatient, string $labResultId): void
{
    // only send the notification if the access level is all
    if ($backendPatient->data_access === "ALL")
    {
        $redis = new Redis();
        $redis->connect("redis", 6379);

        //check if we've already sent a notification for this lab
        //if we did, at least two minutes should have passed before we notify the patient again
        if($redis->exists($labResultId)) {
            $lastNotification = new DateTime($redis->get($labResultId));

            if((new DateTime())->getTimestamp() - $lastNotification->getTimestamp() <= 120) {
                return;
            }
        }

        (new Client())->request('POST', 'http://localhost:8080/publisher/php/sendPushNotificationPerl.php', [
            'form_params' => [
                "patientSerNum" => $backendPatient->legacy_id,
                "ser"           => $labResultId,
                "typeRequest"   => "NewLabResult"
            ]
        ]);

        $redis->set($labResultId, (new DateTime())->format("Y-m-d H:i:s"));
    }
}

function getBackendPatient(Patient $patient): object
{
    $url = get_env_var("NEW_OPALADMIN_HOST_INTERNAL") . '/api/patients/legacy/' . $patient->id . '/';
    $token = get_env_var("NEW_OPALADMIN_TOKEN");
    $client = new Client();

    $response = $client->request("GET", $url, [
        "headers" => [
            "Authorization" => "Token $token"
        ]
    ]);

    $body = (string) $response->getBody();

    return json_decode($body);
}
