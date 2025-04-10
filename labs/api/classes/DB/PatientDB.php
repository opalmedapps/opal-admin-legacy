<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Classes\DB;

require_once __DIR__ . "/../../../../vendor/autoload.php";
require_once __DIR__ . "/../../util/util.php";
use Exception;
use Opal\Labs\Classes\Patient;
use PDO;

/**
 * Class PatientDB
 * @package Opal\Labs\Classes Interface to interact with the OpalDB database
 */
class PatientDB
{
    /**
     * @var PDO OpalDB link
     */
    private $opal_db;

    /**
     * PatientDB constructor.
     * @param PDO $opal_db Link to OpalDB
     */
    public function __construct(PDO $opal_db)
    {
        $this->opal_db = $opal_db;
    }

    /**
     * @return PatientDB
     * @throws Exception
     */
    public static function create(): PatientDB{
        return new PatientDB(OpalDB::getConnection());
    }

    /**
     * Returns list of opal patients, for now it gets the rvh patients.
     * @return array Returns patient array
     */
    function getPatients(): array{
        $query = $this->opal_db->prepare("
            SELECT hpn.PatientSerNum, hpn.MRN, hpn.Hospital_Identifier_Type_Code, pat.RegistrationDate, pat.AccessLevel
            FROM Patient as pat, Patient_Hospital_Identifier as hpn
            WHERE hpn.Hospital_Identifier_Type_Code = 'RVH' AND hpn.Is_Active = 1
              AND pat.PatientSerNum = hpn.PatientSerNum;
        ");
        $query->execute();
        $result = array_map(function($patDB){return Patient::create($patDB);},$query->fetchAll());
        return $result;
    }

    /**
     * @param $mrn
     * @param $site
     * @return Patient
     * @throws Exception
     */
    public function getPatient(string $mrn, string $site)
    {
        $query = $this->opal_db->prepare("
            SELECT hpn.PatientSerNum, hpn.MRN, hpn.Hospital_Identifier_Type_Code, pat.RegistrationDate, pat.AccessLevel
            FROM Patient as pat, Patient_Hospital_Identifier as hpn
            WHERE hpn.MRN = :mrn
              AND hpn.Hospital_Identifier_Type_Code = :site AND hpn.Is_Active = 1
              AND pat.PatientSerNum = hpn.PatientSerNum;
        ");
        $query->execute([":mrn"=>$mrn, ":site"=>$site]);
        $results = $query->fetchAll();
        if(empty($results))
           throw new Exception("Opal patient with Mrn: $mrn, Site: $site not found.");
        return Patient::create($results[0]);
    }
}
