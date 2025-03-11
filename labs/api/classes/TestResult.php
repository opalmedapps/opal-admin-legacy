<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Classes;
class TestResult
{
    public string $mrn;
    public string $site;
    public string $normalRange;
    public ?float $minNorm;
    public ?float $maxNorm;
    public string $abnormalFlag;
    public string $sequenceNum;
    public string $resultDate;
    public string $collectedDate;
    public string $dateAdded;
    public ?string $testGroupCode;
    public string $testCode;
    public string $testValue;
    public ?float $testValueNumeric;
    public ?string $unitDescription;
    public string $availableAt;

    public function __construct()
    {
    }
}
