<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Classes;

require_once __DIR__ . "/../util/util.php";

use DateTime;
use Exception;
use function Opal\Labs\Util\get_date;

class Patient
{
    public DateTime $registrationDate;
    public int $id;
    public string $mrn;
    public string $site;
    public string $accessLevel;

    /**
     * Patient constructor.
     * @param int $patientSerNum
     * @param string $mrn
     * @param string $site
     * @param string $registrationDate
     * @param string $accessLevel
     * @throws Exception if registration string date is not set.
     */
    public function __construct(int $patientSerNum, string $mrn, string $site, string $registrationDate, string $accessLevel)
    {
        $this->id = $patientSerNum;
        $this->mrn = $mrn;
        $this->site = $site;
        $this->registrationDate = get_date($registrationDate);
        $this->accessLevel = $accessLevel;
    }
    // TODO(dherre3) Update to use any mrn, site

    /**
     * @param $patientDB
     * @return Patient
     * @throws Exception if registration string date is not set.
     */
    public static function create($patientDB): Patient {
        return new Patient(
            $patientDB["PatientSerNum"],
            $patientDB["MRN"],
            $patientDB["Hospital_Identifier_Type_Code"],
            $patientDB["RegistrationDate"],
            $patientDB["AccessLevel"]
        );
    }
}
