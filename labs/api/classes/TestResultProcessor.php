<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Classes;

require_once __DIR__ . "/../../../vendor/autoload.php";
require_once __DIR__ . "/../util/util.php";

use DateTime;
use Exception;
use Opal\Labs\Classes\DB\OpalDB;
use Opal\Labs\Classes\DB\PatientDB;
use PDO;
use function Opal\Labs\Util\{get_date, format_date};

class TestResultProcessor {

    private $oasis_test_results;
    private $opal_db;
    /**
     * @var PatientDB
     */
    private PatientDB $patient_db;

    function __construct(PDO $opalDB, OasisInterface $oasisTestResults, PatientDB $patient_db)
    {
        $this->opal_db = $opalDB;
        $this->oasis_test_results = $oasisTestResults;
        $this->patient_db = $patient_db;
    }

    /**
     * @return TestResultProcessor
     * @throws Exception
     */
    public static function create(): TestResultProcessor {
        $test_result_processor = new TestResultProcessor(
                OpalDB::getConnection(),
                OasisInterface::create(),
                PatientDB::create());
        return $test_result_processor;
    }

    public function processPatient(Patient $patient, DateTime $fromDate=NULL,DateTime $toDate=NULL): array
    {
        if($fromDate === NULL){
            $fromDate = (clone $patient->registrationDate)->modify('-1 year');
        }
        if($toDate === NULL){
            $toDate = get_date("now");
        }
        $result = [];
        $result["patientSerNum"] = $patient->id;
        $result["fromDate"]= $fromDate->format("Y-m-d");
        $result["toDate"]= $toDate->format("Y-m-d");
        return array_merge($result, $this->process($patient->mrn, $patient->site, $fromDate, $toDate));
    }


    public function processPatients(DateTime $fromDate=NULL, DateTime $toDate=NULL): array{
        $patients = $this->patient_db->getPatients();
        $results = [];
        foreach($patients as $patient){
            $results[] = $this->processPatient($patient, $fromDate, $toDate);
        }
        return $results;
    }
    /**
     * Fetches results between the $fromDate, $toDate for the patient matching id ($mrn, $site)
     * @param string $mrn
     * @param string $site
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @return array
     */
    public function process(string $mrn, string $site, DateTime $fromDate, DateTime $toDate):array {
        $result = array(
            "success" => false,
            "insertedRows" => 0,
            "updatedRows" => 0,
            "publishedRow" => false,
            "error" => NULL
        );
        try{
            $oasis_results = $this->oasis_test_results->getLabResultsByMrnSite($mrn, $site,
                $fromDate, $toDate);
            if(empty($oasis_results)){
                throw new Exception("Empty Oasis Test Results for parameters: (mrn:$mrn, site:$site, fromDate:"
                    .format_date($fromDate).", toDate:".format_date($toDate));
            }
            foreach ($oasis_results as $lab_result) {
                if ($this->is_test_group($lab_result)) {
                    $this->update_test_groups(
                        $lab_result->servTypeChildAcro,
                        $lab_result->servTypeChildLongDesc
                    );
                } elseif ($this->is_test_component($lab_result)) {
                    // Add TestCode and TestName to TestExpression (Definition table), if it doesn't already exist
                    $this->update_test_components($lab_result->servTypeChildAcro,$lab_result->servTypeChildLongDesc);
                    $updateResult = $this->update_test_result($mrn, $site, $lab_result);
                    // Check if the result has had any effect on the tables
                    if ($updateResult->rowCount() === 1) $result["insertedRows"]++;
                    if ($updateResult->rowCount() === 2) $result["updatedRows"]++;

                    if($this->checkIfLabIsPublished($lab_result->servTypeChildAcro) === true) $result["publishedRow"] = true;
                }
            }
            // Note that an empty oasis query means that Oasis does not have this results yet so the request is still
            // PENDING, however, an Oasis results larger than 1 with no OpalDB updates means we
            // have already processed the results.
            $result["success"] = $result["insertedRows"] > 0 || $result ["updatedRows"] > 0 || !empty($oasis_results);
        }catch(Exception $err){
            $result["error"] = $err;
        }
        return $result;
    }

    //////////////////////////////////////////////////

    private function update_test_result(string $mrn, string $site, $lab_result)
    {
        $test_result = (new TestResultBuilder)->setMrn($mrn)->setSite($site)->
                    setTestGroupCode($lab_result->servTypeRootAcro,$lab_result->result)->
                    setTestCode($lab_result->servTypeChildAcro)->
                    setTestValue($lab_result->result)->
                    setResultDate($lab_result->resultDate)->
                    setCollectedDate($lab_result->collectedDate)->
                    setDateAdded(get_date("now"))->
                    setSequenceNum($lab_result->sortChildSeq ?? NULL)->
                    setNormalRange($lab_result->rangeReference ?? "")->
                    setAbnormalFlag($lab_result->rangeIndicator ?? "")->
                    setUnitDescription($lab_result->resultUnit ?? "")->
                    setAvailableAt($lab_result->resultDate)->build();

        return $this->update_test_result_to_db($test_result);
    }

    private function update_test_result_to_db(TestResult $test_result)
    {
        // NOTE: This query is duplicated in processLabForPatient.php
        $sql = $this->opal_db->prepare("
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
                SequenceNum,
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
                :sequenceNum,
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
        ");

        $sql->execute(array(
            ":mrn" => $test_result->mrn,
            ":site" => $test_result->site,
            ":testCode" => $test_result->testCode,
            ":testGroupCode" => $test_result->testGroupCode,
            ":testDate" => $test_result->collectedDate,
            ":testValue" => $test_result->testValue,
            ":testValueNumeric" => $test_result->testValueNumeric,
            ":resultDate" => $test_result->resultDate,
            ":unitDescription" => $test_result->unitDescription,
            ":normalRange" => $test_result->normalRange,
            ":minNorm" => $test_result->minNorm,
            ":maxNorm" => $test_result->maxNorm,
            ":abnormalFlag" => $test_result->abnormalFlag,
            ":sequenceNum" => $test_result->sequenceNum,
            ":dateAdded" => $test_result->dateAdded,
            ":availableAt" => $test_result->availableAt,
        ));
        return $sql;
    }
    private function is_test_component($lab_result): bool
    {
        return !empty($lab_result->servTypeChildAcro) && !empty($lab_result->servTypeChildLongDesc)
                    && !empty($lab_result->result);
    }
    private function is_test_group($lab_result): bool
    {
        return empty($lab_result->result) &&
            !empty($lab_result->servTypeChildAcro) && !empty($lab_result->servTypeRootAcro)
            && $lab_result->servTypeChildAcro === $lab_result->servTypeRootAcro;
    }
    private function update_test_groups(string $test_code, string $test_name)
    {
        //check if the group already exists
        //if it does, update it, otherwise insert a new record

        $groupQuery = $this->opal_db->prepare("
            SELECT
                ExpressionName
            FROM
                TestGroupExpression
            WHERE
                TestCode = :testCode
        ");
        $groupQuery->execute([":testCode" => $test_code]);

        $group = $groupQuery->fetchAll()[0] ?? NULL;

        if($group === NULL) {
            $this->opal_db->prepare("
                INSERT INTO TestGroupExpression
                    (TestCode, ExpressionName)
                VALUES
                    (:testCode, :testName)
            ")->execute([
                ":testCode" => $test_code,
                ":testName" => $test_name
            ]);
        }
    }
    public function update_test_components($test_code, $test_name)
    {
        //check if the component already exists
        //if it does, update it, otherwise insert a new record

        $componentQuery = $this->opal_db->prepare("
            SELECT
                ExpressionName
            FROM
                TestExpression
            WHERE
                TestCode = :testCode
        ");
        $componentQuery->execute([":testCode" => $test_code]);

        $component = $componentQuery->fetchAll()[0] ?? NULL;

        if($component === NULL) {
            $this->opal_db->prepare("
                INSERT INTO TestExpression
                    (TestCode, ExpressionName)
                VALUES
                    (:testCode, :testName)
            ")->execute([
                ":testCode" => $test_code,
                ":testName" => $test_name
            ]);
        }
    }

    private function checkIfLabIsPublished(string $test_code): bool
    {
        $query = $this->opal_db->prepare("
            SELECT
                TC.PublishFlag
            FROM
                TestControl TC
                INNER JOIN TestExpression TE ON TE.TestControlSerNum = TC.TestControlSerNum
                    AND TE.TestCode = ?
        ");

        $query->execute([$test_code]);

        return (bool) ($query->fetchAll()[0]["PublishFlag"] ?? null);
    }
}
