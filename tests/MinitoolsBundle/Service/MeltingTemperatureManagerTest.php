<?php

namespace Tests\MinitoolsBundle\Service;

use AppBundle\Service\NucleotidsManager;
use MinitoolsBundle\Service\MeltingTemperatureManager;
use PHPUnit\Framework\TestCase;

class MeltingTemperatureManagerTest extends TestCase
{
    protected $enthalpyValues;

    protected $apiMock;

    protected $enthropyValues;

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

        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiMock = $this->getMockBuilder('AppBundle\Bioapi\Bioapi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getEnthalpyValues','getEnthropyValues'])
            ->getMock();
        $this->apiMock->method("getEnthalpyValues")->will($this->returnValue($this->enthalpyValues));
        $this->apiMock->method("getEnthropyValues")->will($this->returnValue($this->enthropyValues));
    }

    public function testCalculateCG()
    {
        $primer = "AAAATTTGGGGCCCATGCCC";
        $fExpected = 55.0;

        $nucleoMock = new NucleotidsManager();

        $service = new MeltingTemperatureManager($nucleoMock, $this->apiMock);
        $testFunction = $service->calculateCG($primer);

        $this->assertEquals($testFunction, $fExpected);
    }
}