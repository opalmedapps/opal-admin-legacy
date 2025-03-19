<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Classes;

require_once __DIR__ . "/../../../vendor/autoload.php";
require_once __DIR__ . "/../util/util.php";

use DateTime;
use Exception;
use SoapClient;
use SoapFault;

Config::setEnvironment();

class OasisInterface
{

    private SoapClient $oasis_soap_client;

    /**
     * OasisTestResult constructor.
     * @param SoapClient $soapClient
     */
    function __construct(SoapClient $soapClient)
    {
        $this->oasis_soap_client = $soapClient;
    }

    /**
     * @throws Exception
     */
    public static function create(): OasisInterface
    {
        if ($_ENV["LABS_OASIS_WSDL_URL"] === false)
            throw new Exception("Must set LABS_OASIS_WSDL_URL as environment variable");
        try {
            return new OasisInterface(new SoapClient($_ENV["LABS_OASIS_WSDL_URL"], ['trace' => 1]));
        } catch (SoapFault $e) {
            throw new Exception("SoapClient failed to instantiate:\n" . $e->getMessage());
        }
    }
    /**
     * @param string $mrn MRN for patient
     * @param string $site Hospital Site
     * @param DateTime $fromDate Date to fetch the results from
     * @param DateTime $toDate Date to fetch the results to
     * @return array Returns TestResults array from oasis.
     * @throws Exception throws exception if OasisPatientID is not found, or there is a problem getting the lab results
     */
    public function getLabResultsByMrnSite(string $mrn, string $site, DateTime $fromDate, DateTime $toDate): array
    {
        $site = $this->getSite($site);
        $oasis_patient_id = $this->getOasisPatient($mrn, $site)->internalId;
        return $this->getLabResults($oasis_patient_id, $fromDate, $toDate);
    }

    public function getLabResults(string $oasis_patient_id, DateTime $fromDate, DateTime $toDate): array
    {
        $params = array(
            'arg0' => $oasis_patient_id,
            'arg1' => $fromDate->format("Y-m-d"),
            'arg2' => $toDate->format("Y-m-d")
        );
        $response = $this->oasis_soap_client->getLabList($params);
        $response = $response->return ?? NULL;
        // if it is an OBJECT (NOT an array), that means it contains only ONE <return> result in the xml.
        // In this case foreach does not work properly and does not access property values properly, like $lab->result
        // converting to an array is the solution.
        // If it is not an object and it is already an array, that means it contains more than on <return> value in the xml returned.
        // Summary:
        // If return value type is OBJECT, then it contains only "one" <return> value, convert it to ARRAY.
        // If already any ARRAY, then it contains "multiple" <return> values
        if (empty($response)) {
            $response = [];
        }
        elseif (is_object($response)) {
            $response = array($response);
        }

        $response = array_map(function($x) {
            $x->servTypeChildAcro = preg_replace("/^MX/","",$x->servTypeChildAcro);
            $x->servTypeChildAcro = preg_replace("/\*$/","",$x->servTypeChildAcro);

            $x->servTypeRootAcro = preg_replace("/^MX/","",$x->servTypeRootAcro);
            $x->servTypeRootAcro = preg_replace("/\*$/","",$x->servTypeRootAcro);

            return $x;
        },$response);

        return $response;
    }

    /**
     * @param string $mrn Patient MRN
     * @param string $site Hospital Id
     * @return Object Returns the OasisPatient given an (mrn,site)
     * @throws Exception Throws exception if
     */
    public function getOasisPatient(string $mrn, string $site): Object
    {
        $params = array(
            'arg0' => $site,
            'arg1' => $mrn
        );
        $response = null;
        try {
            $response = $this->oasis_soap_client->getPatientByMrnSite($params);
        } catch (Exception $e) {
            throw new Exception("Unable to getPatientByMrnSite() for parameters," .
                "site: $site, mrn: $mrn, error: " . $e->getMessage());
        }
        if (empty($response->return)) {
            throw new Exception("Patient not found in Oasis with " .
                "site: $site, mrn: $mrn");
        }
        return $response->return;
    }

    public function getSite(string $site): string
    {
        $site = strtoupper($site);
        switch ($site) {
            case "RVH":
            case "MNH":
            case "MCI":
                $site = "MR_PCS";
                break;
            case "MGH":
                $site = "MG_PCS";
                break;
            case "MCH":
                $site = "MC_ADT";
                break;
            case "LAC":
                $site = "LC_ADT";
                break;
            default:  // if Site is not one of the above, then use it as is. (could be already received as MR_PCS, MG_PCS etc..)
                break;
        }
        return $site;
    }
}
