<?php
/**
 * Created by PhpStorm.
 * User: amelaye
 * Date: 2019-07-23
 * Time: 11:34
 */

namespace Tests\MinitoolsBundle\Service;

use MinitoolsBundle\Service\ChaosGameRepresentationManager;
use MinitoolsBundle\Service\DistanceAmongSequencesManager;
use PHPUnit\Framework\TestCase;


class DistanceAmongSequenciesTest extends TestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function testFormatSequences()
    {
        $argument = "GTGCCGAGCTGAGTTCCTTATAAGAATTAATCTTAATTTTGTATTTTTTCCTGTAAGACAATAGGCCATG";

        $aExpected = array(
            0 => "GTGCCGAGCTGAGTTCCTTATAAGAATTAATCTTAATTTTGTATTTTTTCCTGTAAGACAATAGGCCATG"
        );

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

        $oligoMock = $this->getMockBuilder('AppBundle\Service\OligosManager')
            ->setConstructorArgs([$apiMock])
            ->setMethods()
            ->getMock();

        $service = new DistanceAmongSequencesManager($oligoMock, $apiMock);
        $testFunction = $service->formatSequences($argument);

        $this->assertEquals($aExpected, $testFunction);
    }

    public function testComputeOligonucleotidsFrequenciesEuclidean()
    {
        $aSeq = [0 => "GTGCCGAGCTGAGTTCCTTATAAGAATTAATCTTAATTTTGTATTTTTTCCTGTAAGACAATAGGCCATG"];
        $iLen = 2;

        $aExpected = array(
            0 => array(
                "AA" => 2.0869565217391,
                "AC" => 0.57971014492754,
                "AG" => 1.0434782608696,
                "AT" => 1.6231884057971,
                "CA" => 0.81159420289855,
                "CC" => 0.57971014492754,
                "CG" => 0.23188405797101,
                "CT" => 1.0434782608696,
                "GA" => 0.81159420289855,
                "GC" => 0.69565217391304,
                "GG" => 0.57971014492754,
                "GT" => 0.57971014492754,
                "TA" => 1.6231884057971,
                "TC" => 0.81159420289855,
                "TG" => 0.81159420289855,
                "TT" => 2.0869565217391,
            )
        );

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

        $oligoMock = $this->getMockBuilder('AppBundle\Service\OligosManager')
            ->setConstructorArgs([$apiMock])
            ->setMethods()
            ->getMock();

        $service = new DistanceAmongSequencesManager($oligoMock, $apiMock);
        $testFunction = $service->computeOligonucleotidsFrequenciesEuclidean($aSeq, $iLen);

        $this->assertEquals($aExpected, $testFunction);
    }
}
