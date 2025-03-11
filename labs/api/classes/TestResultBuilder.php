<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Classes;

require_once __DIR__ . "/../util/util.php";

use function Opal\Labs\Util\format_date_string;

use DateTime;
use Exception;

class TestResultBuilder
{
    const  DATE_FORMAT = "Y-m-d H:i:s";
    public string $mrn;
    public string $site;
    public string $normalRange;
    public ?float $minNorm;
    public ?float $maxNorm;
    public string $abnormalFlag;
    public ?string $sequenceNum;
    public string $resultDate;
    public string $collectedDate;
    public string $dateAdded;
    public ?string $testGroupCode;
    public string $testCode;
    public string $testValue;
    public ?float $testValueNumeric;
    public string $unitDescription;
    public string $availableAt;

    public function setMrn(string $mrn): TestResultBuilder
    {
        $this->mrn = $mrn;
        return $this;
    }

    public function setNormalRange(string $range)
    {
        $this->normalRange = $range;
        [$this->minNorm, $this->maxNorm] = $this::get_normal_range_numeric_extremes($range);
        return $this;
    }
    public function setSite(string $site)
    {
        $this->site = $site;
        return $this;
    }
    public function setDateAdded(DateTime $date){
        $this->dateAdded = $date->format($this::DATE_FORMAT);
        return $this;
    }

    public function setSequenceNum(?string $sequenceNum)
    {
        $this->sequenceNum = $sequenceNum;
        return $this;
    }

    public function setAbnormalFlag(string $abnormalFlag)
    {
        $this->abnormalFlag = $abnormalFlag;
        return $this;
    }

    public function setResultDate(string $date)
    {
        $this->resultDate = format_date_string($date, $this::DATE_FORMAT);
        return $this;
    }
    public function setTestCode(string $testCode)
    {
        $this->testCode = $testCode;
        return $this;
    }
    public function setTestValue(string $testValue)
    {
        $this->testValue = $testValue;
        $this->testValueNumeric = is_numeric($testValue) ? floatval($testValue) : null;
        return $this;
    }

    public function setCollectedDate(string $collectedDate)
    {
        $this->collectedDate = format_date_string($collectedDate, $this::DATE_FORMAT);
        return $this;
    }
    public function setTestGroupCode(string $testGroupCode, string $result=NULL)
    {
        if(!empty($result)) {
            $this->testGroupCode = $testGroupCode;
        }
        else {
            $this->testGroupCode = NULL;
        }

        return $this;
    }
    public function setUnitDescription(string $unitDescription)
    {
        $this->unitDescription = $unitDescription;
        return $this;
    }
    public function setAvailableAt(string $availableAt)
    {
        $this->availableAt = format_date_string($availableAt, $this::DATE_FORMAT);
        return $this;
    }

    private static function get_normal_range_numeric_extremes($range): array
    {
        if (empty($range)) return [null, null];
        $pos = strrpos($range, "-");
        if ($pos > 0) {
            try {
                $minString = substr($range, 0, $pos);
                $maxString = substr($range, $pos + 1);
                return [
                    is_numeric($minString) ? (float) $minString : null,
                    is_numeric($maxString) ? (float) $maxString : null
                ];
            } catch (Exception $e) {
                return [null, null];
            }
        }
        return [null, null];
    }
    public function build(): TestResult
    {
        $test = new TestResult;
        $test->mrn = $this->mrn;
        $test->site = $this->site;
        $test->normalRange = $this->normalRange;
        $test->minNorm = $this->minNorm;
        $test->maxNorm = $this->maxNorm;
        $test->sequenceNum = $this->sequenceNum;
        $test->resultDate = $this->resultDate;
        $test->collectedDate = $this->collectedDate;
        $test->testGroupCode = $this->testGroupCode;
        $test->testValue = $this->testValue;
        $test->abnormalFlag = $this->abnormalFlag;
        $test->testCode = $this->testCode;
        $test->testValueNumeric = $this->testValueNumeric;
        $test->unitDescription = $this->unitDescription;
        $test->dateAdded = $this->dateAdded;
        $test->availableAt = $this->availableAt;
        return $test;
    }
}
