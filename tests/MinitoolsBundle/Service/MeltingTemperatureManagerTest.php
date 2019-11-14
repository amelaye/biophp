<?php

namespace Tests\MinitoolsBundle\Service;

use AppBundle\Service\Misc\NucleotidsManager;
use MinitoolsBundle\Service\MeltingTemperatureManager;
use PHPUnit\Framework\TestCase;

class MeltingTemperatureManagerTest extends TestCase
{
    protected $enthalpyValues;

    protected $apiMock;

    protected $enthropyValues;

    protected $nucleoMock;

    public function setUp()
    {
        $this->enthalpyValues = [
            "AA" => -7.9,
            "AC" => -8.4,
            "AG" => -7.8,
            "AT" => -7.2,
            "CA" => -8.5,
            "CC" => -8,
            "CG" => -10.6,
            "CT" => -7.8,
            "GA" => -8.2,
            "GC" => -9.8,
            "GG" => -8,
            "GT" => -8.4,
            "TA" => -7.2,
            "TC" => -8.2,
            "TG" => -8.5,
            "TT" => -7.9,
        ];

        $this->enthropyValues = [
            "AA" => -22.2,
            "AC" => -22.4,
            "AG" => -21,
            "AT" => -20.4,
            "CA" => -22.7,
            "CC" => -19.9,
            "CG" => -27.2,
            "CT" => -21,
            "GA" => -22.2,
            "GC" => -24.4,
            "GG" => -19.9,
            "GT" => -22.4,
            "TA" => -21.3,
            "TC" => -22.2,
            "TG" => -22.7,
            "TT" => -22.2
        ];

        $water = [
            "@context" => "/contexts/Element",
            "@id" => "/elements/6",
            "@type" => "Element",
            "id" => 6,
            "name" => "water",
            "weight" => 18.015
        ];

        $aDnaWeights = [
          "A" => 313.245,
          "T" => 304.225,
          "G" => 329.245,
          "C" => 289.215,
        ];

        $aRnaWeights = [
          "A" => 329.245,
          "U" => 306.195,
          "G" => 345.245,
          "C" => 305.215
        ];


        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiMock = $this->getMockBuilder('AppBundle\Bioapi\Bioapi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getEnthalpyValues','getEnthropyValues','getWater','getDNAWeight','getRNAWeight'])
            ->getMock();
        $this->apiMock->method("getEnthalpyValues")->will($this->returnValue($this->enthalpyValues));
        $this->apiMock->method("getEnthropyValues")->will($this->returnValue($this->enthropyValues));
        $this->apiMock->method("getWater")->will($this->returnValue($water));
        $this->apiMock->method("getDNAWeight")->will($this->returnValue($aDnaWeights));
        $this->apiMock->method("getRNAWeight")->will($this->returnValue($aRnaWeights));

        $this->nucleoMock = new NucleotidsManager();
    }

    public function testCalculateCG()
    {
        $primer = "AAAATTTGGGGCCCATGCCC";
        $fExpected = 55.0;

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $testFunction = $service->calculateCG($primer);

        $this->assertEquals($testFunction, $fExpected);
    }

    public function testCalculateCGException()
    {
        $this->expectException(\Exception::class);
        $primer = [];

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $service->calculateCG($primer);
    }

    public function testTmBaseStacking()
    {
        $primer = "AAAATTTGGGGCCCATGCCC";
        $concPrimer = "200";
        $concSalt = "50";
        $concMg = "2";

        $aExpected = [
            "tm" => 68.6,
            "enthalpy" => -152.6,
            "entropy" => -414.45,
        ];

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $testFunction = $service->tmBaseStacking($primer, $concPrimer, $concSalt, $concMg);

        $this->assertEquals($testFunction, $aExpected);
    }

    public function testTmBaseStackingException()
    {
        $this->expectException(\Exception::class);
        $primer = [];
        $concPrimer = "200";
        $concSalt = "50";
        $concMg = "2";

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $service->tmBaseStacking($primer, $concPrimer, $concSalt, $concMg);
    }

    public function testTmMinMoreFourteen()
    {
        $primer = "AAAATTTGGGGCCCATGCCC";
        $fExpected =  53.8;

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $testFunction = $service->tmMin($primer);

        $this->assertEquals($testFunction, $fExpected);
    }

    public function testTmMinLessFourteen()
    {
        $primer = "AAAATTT";
        $fExpected =  14.0;

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $testFunction = $service->tmMin($primer);

        $this->assertEquals($testFunction, $fExpected);
    }

    public function testTmMinException()
    {
        $this->expectException(\Exception::class);
        $primer = [];

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $service->tmMin($primer);
    }

    public function testTmMaxMoreFourteen()
    {
        $primer = "AAAATTTGGGGCCCATGCCC";
        $fExpected =  53.8;

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $testFunction = $service->tmMax($primer);

        $this->assertEquals($testFunction, $fExpected);
    }

    public function testTmMaxLessFourteen()
    {
        $primer = "GAGAGA";
        $fExpected =  18.0;

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $testFunction = $service->tmMax($primer);

        $this->assertEquals($testFunction, $fExpected);
    }

    public function testTmMaxException()
    {
        $this->expectException(\Exception::class);
        $primer = [];
        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $service->tmMax($primer);
    }

    public function testMolwtUpperLimit()
    {
        $sSequence = "AAAATTTGGGGCCCATGCCC";
        $sMoltype = "DNA";
        $sLimit = "upperlimit";

        $fExpected = 6182.655;

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $testFunction = $service->molwt($sSequence, $sMoltype, $sLimit);

        $this->assertEquals($testFunction, $fExpected);
    }

    public function testMolwtLowerLimit()
    {
        $sSequence = "AAAATTTGGGGCCCATGCCC";
        $sMoltype = "DNA";
        $sLimit = "lowerlimit";

        $fExpected = 6182.655;

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $testFunction = $service->molwt($sSequence, $sMoltype, $sLimit);

        $this->assertEquals($testFunction, $fExpected);
    }

    public function testMolwtLowerLimitTest()
    {
        $this->expectException(\Exception::class);
        $sSequence = [];
        $sMoltype = "DNA";
        $sLimit = "lowerlimit";

        $service = new MeltingTemperatureManager($this->nucleoMock, $this->apiMock);
        $service->molwt($sSequence, $sMoltype, $sLimit);
    }
}