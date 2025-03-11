<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

use Opal\Labs\Classes\OasisInterface;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;

// Get around to mock methods in a SoapClient
abstract class SoapClientOasis extends SoapClient{
    public abstract function getPatientByMrnSite(array $args): OasisQuery;
    public abstract function getLabList(array $args): OasisQuery;
}
class OasisQuery {
    public ?object $return;
    function __construct(?object $arb)
    {
        $this->return = $arb;
    }
}
class OasisPatient {
    public string $internalId;
    public function __construct(string $patientId)
    {
        $this->internalId = $patientId;
    }
}
class OasisInterfaceTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SoapClient
     */
    private $mock_soap;
    /**
     * @var OasisInterface
     */
    private $oasis_interface;

    public function setUp(): void
    {
        $this->mock_soap = $this->createMock(SoapClientOasis::class);
        $this->oasis_interface = new OasisInterface($this->mock_soap);
    }

    public function testGetSiteShouldReturnCorrectSites()
    {
        $inputs = [ "RVH", "MNH", "MCI", "MGH", "MCH", "LAC", "OTHER"];
        $expected = ["MR_PCS", "MR_PCS", "MR_PCS", "MG_PCS", "MC_ADT", "LC_ADT", "OTHER"];
        $this->assertEquals(array_map(function($site){return $this->oasis_interface->getSite($site);}, $inputs),
            $expected);
    }

    public function testGetOasisPatientIdShouldThrowErrorWhenBadParametersPassedToOasis()
    {
        $this->mock_soap->expects($this->once())->method("getPatientByMrnSite")->
            willThrowException(new Exception("Bad Parameters"));
        $this->expectException(Exception::class);
        $this->oasis_interface->getOasisPatient("asdas", "sadas");
    }

    public function testGetOasisPatientIdShouldThrowErrorWhenPatientNotFound()
    {
        $site =  "asdas";
        $mrn = "asdas";
        $this->mock_soap->expects($this->once())->method("getPatientByMrnSite")->
        willReturn(new OasisQuery(NULL));
        $this->expectExceptionMessage("Patient not found in Oasis with site: $site, mrn: $mrn");
        $this->oasis_interface->getOasisPatient($mrn, $site);
    }

    public function testGetOasisPatientShouldCallAndReturnSoapClientGetPatientByMrnSite()
    {
        $expected = new OasisPatient("id");
        $args = ["arg0"=>"sadas","arg1"=>"asdas"];
        $this->mock_soap->expects($this->once())->method("getPatientByMrnSite")->
            with($args)->
        willReturn(new OasisQuery($expected));
        $actual = $this->oasis_interface->getOasisPatient("asdas", "sadas");
        $this->assertEquals($expected, $actual);
    }

    public function testGetLabResultsShouldCallSoapClientGetLabList()
    {
        $args = ["arg0"=>"patientId","arg1"=>"2020-05-20", "arg2"=>"2020-05-20" ];
        $this->mock_soap->expects($this->once())->method("getLabList")->with($args)->
        willReturn(new OasisQuery(NULL));
        $this->oasis_interface->getLabResults($args["arg0"], new DateTime($args["arg1"]),
            new DateTime($args["arg2"]));
    }

    public function testGetLabResultsShouldReturnEmptyArrayWhenOasisResultsAreEmpty()
    {
        $expected = [];
        $this->mock_soap->expects($this->once())->method("getLabList")->
        willReturn(new OasisQuery(NULL));
        $actual = $this->oasis_interface->getLabResults("adsa", new DateTime("now"),
            new DateTime("now"));
        $this->assertEquals($expected, $actual);
    }

    // public function testGetLabResultsShouldReturnArrayWhenOasisResultsAreObject()
    // {
    //     $expected = [new OasisPatient("dav")];
    //     $this->mock_soap->expects($this->once())->method("getLabList")->
    //     willReturn(new OasisQuery($expected[0]));
    //     $actual = $this->oasis_interface->getLabResults("adsa", new DateTime("now"),
    //         new DateTime("now"));
    //     $this->assertEquals($expected, $actual);
    // }

    // public function testGetLabResultsByMrnSiteShouldCallOasisFunctionsCorrectly()
    // {
    //     $mrn = "mrn";
    //     $site = "RVH";
    //     $fromDate = new DateTime("2020-05-20");
    //     $toDate = new DateTime("2020-05-20");
    //     $expected = [new OasisPatient("labs")];
    //     $oasis_query = new OasisQuery($expected[0]);
    //     $this->mock_soap->method("getLabList")->
    //     willReturn($oasis_query);
    //     $this->mock_soap->method("getPatientByMrnSite")->
    //     willReturn($oasis_query);
    //     $actual = $this->oasis_interface->getLabResultsByMrnSite($mrn, $site, $fromDate, $toDate);
    //     $this->assertEquals($expected, $actual);
    // }
}