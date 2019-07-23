<?php
/**
 * Created by PhpStorm.
 * User: amelaye
 * Date: 2019-07-23
 * Time: 11:34
 */

namespace Tests\MinitoolsBundle\Service;

use AppBundle\Bioapi\Bioapi;
use MinitoolsBundle\Service\ChaosGameRepresentationManager;
use PHPUnit\Framework\TestCase;


class ChaosGameRepresentationManagerTest extends TestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function testNumberNucleos()
    {
        $aSeqdata =  [
            "sequence" => "GTGCCGAGCTGAGTTCCTTATAAGAATTAATCTTAATTTTGTATTTTTTCCTGTAAGACAATAGGCCATGTTAATTAAACTGAAGAAG",
            "length" => "2"
        ];
        $aExpected = ["T" => 32, "A" => 28, "C" => 12, "G" => 16];

        $aNucleotidGraph = [
            "positions_2" => ["h_pos" => 24, "v_pos" => 26],
            "intervals_2" => 64,
            "startx_2" => 10,
            "starty_2" => 90,
            "fcgr_file" => 'FCGR.png',
            "cgr_file" => 'CGR.png',
            "dendogram_file" => 'dendogram.png'
        ];

        $dnaComplement = ["A" => "T", "T" => "A", "G" => "C", "C" => "G"];

        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();


        $apiMock = $this->getMockBuilder('AppBundle\Bioapi\Bioapi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getDNAComplement'])
            ->getMock();
        $apiMock->method("getDNAComplement")->will($this->returnValue($dnaComplement));

        $service = new ChaosGameRepresentationManager($aNucleotidGraph, $apiMock);
        $testFunction = $service->numberNucleos($aSeqdata);

        $this->assertEquals($aExpected, $testFunction);
    }
}
