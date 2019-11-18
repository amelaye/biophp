<?php
/**
 * Created by PhpStorm.
 * User: amelaye
 * Date: 2019-07-23
 * Time: 11:34
 */

namespace Tests\MinitoolsBundle\Service;

use MinitoolsBundle\Service\ChaosGameRepresentationManager;
use PHPUnit\Framework\TestCase;


class ChaosGameRepresentationManagerTest extends TestCase
{
    protected $dnaComplement;

    protected $apiMock;

    protected $aNucleotidGraph;

    protected function setUp()
    {
        $this->dnaComplement = ["A" => "T", "T" => "A", "G" => "C", "C" => "G"];

        $this->aNucleotidGraph = [
            "positions_2" => ["h_pos" => 24, "v_pos" => 26],
            "intervals_2" => 64,
            "startx_2" => 10,
            "starty_2" => 90,
            "fcgr_file" => 'FCGR.png',
            "cgr_file" => 'CGR.png',
            "dendogram_file" => 'dendogram.png'
        ];

        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();


        $this->apiMock = $this->getMockBuilder('AppBundle\Api\Bioapi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getDNAComplement'])
            ->getMock();
        $this->apiMock->method("getDNAComplement")->will($this->returnValue($this->dnaComplement));
    }

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

        $service = new ChaosGameRepresentationManager($this->aNucleotidGraph, $this->apiMock);
        $testFunction = $service->numberNucleos($aSeqdata);

        $this->assertEquals($aExpected, $testFunction);
    }

    public function testCreateFCGRImageWithOligoLen2()
    {
        $oligos = [
            "AA" => 98,
            "AC" => 24,
            "AG" => 36,
            "AT" => 66,
            "CA" => 39,
            "CC" => 21,
            "CG" => 4,
            "CT" => 36,
            "GA" => 38,
            "GC" => 18,
            "GG" => 21,
            "GT" => 24,
            "TA" => 50,
            "TC" => 38,
            "TG" => 39,
            "TT" => 98,
        ];

        $seq_name = "aaa";

        $aNucleotids = [
            "T" => 225,
            "A" => 225,
            "C" => 101,
            "G" => 101,
        ];

        $seq_len = 326;

        $n = 2;

        $oligo_len = "2";

        $aExpected = [
            "AA" => [0 => 10, 1 => 282, 2 => 73, 3 => 345],
            "AC" => [0 => 10, 1 => 154, 2 => 73, 3 => 217],
            "AG" => [0 => 138, 1 => 154, 2 => 201, 3 => 217],
            "AT" => [0 => 138, 1 => 282, 2 => 201, 3 => 345],
            "CA" => [0 => 10, 1 => 218, 2 => 73, 3 => 281],
            "CC" => [0 => 10, 1 => 90, 2 => 73, 3 => 153],
            "CG" => [0 => 138, 1 => 90, 2 => 201, 3 => 153],
            "CT" => [0 => 138, 1 => 218, 2 => 201, 3 => 281],
            "GA" => [0 => 74, 1 => 218, 2 => 137, 3 => 281],
            "GC" => [0 => 74, 1 => 90, 2 => 137, 3 => 153],
            "GG" => [0 => 202, 1 => 90, 2 => 265, 3 => 153],
            "GT" => [0 => 202, 1 => 218, 2 => 265, 3 => 281],
            "TA" => [0 => 74, 1 => 282, 2 => 137, 3 => 345],
            "TC" => [0 => 74, 1 => 154, 2 => 137, 3 => 217],
            "TG" => [0 => 202, 1 => 154, 2 => 265, 3 => 217],
            "TT" => [0 => 202, 1 => 282, 2 => 265, 3 => 345]
        ];

        $service = new ChaosGameRepresentationManager($this->aNucleotidGraph, $this->apiMock);
        $testFunction = $service->createFCGRImage($oligos, $seq_name, $aNucleotids, $seq_len, $n, $oligo_len);

        $this->assertEquals($aExpected, $testFunction);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateFCGRImageException()
    {
        $this->expectException(\Exception::class);
        $oligos = [];
        $seq_name = "aaa";
        $aNucleotids = [];
        $seq_len = 326;
        $n = 2;
        $oligo_len = "2";

        $service = new ChaosGameRepresentationManager($this->aNucleotidGraph, $this->apiMock);
        $service->createFCGRImage($oligos, $seq_name, $aNucleotids, $seq_len, $n, $oligo_len);
    }

    public function testCreateFCGRImageWithOligoLen3()
    {
        $oligos = [
          "AAA" => 0,
          "AAC" => 0,
          "AAG" => 2,
          "AAT" => 4,
          "ACA" => 1,
          "ACC" => 0,
          "ACG" => 0,
          "ACT" => 0,
          "AGA" => 2,
          "AGC" => 1,
          "AGG" => 1,
          "AGT" => 1,
          "ATA" => 2,
          "ATC" => 1,
          "ATG" => 0,
          "ATT" => 3,
          "CAA" => 1,
          "CAC" => 0,
          "CAG" => 0,
          "CAT" => 0,
          "CCA" => 0,
          "CCC" => 0,
          "CCG" => 1,
          "CCT" => 2,
          "CGA" => 1,
          "CGC" => 0,
          "CGG" => 0,
          "CGT" => 0,
          "CTA" => 0,
          "CTC" => 0,
          "CTG" => 2,
          "CTT" => 2,
          "GAA" => 1,
          "GAC" => 1,
          "GAG" => 2,
          "GAT" => 0,
          "GCA" => 0,
          "GCC" => 1,
          "GCG" => 0,
          "GCT" => 1,
          "GGA" => 0,
          "GGC" => 0,
          "GGG" => 0,
          "GGT" => 0,
          "GTA" => 2,
          "GTC" => 0,
          "GTG" => 1,
          "GTT" => 1,
          "TAA" => 4,
          "TAC" => 0,
          "TAG" => 1,
          "TAT" => 2,
          "TCA" => 0,
          "TCC" => 2,
          "TCG" => 0,
          "TCT" => 1,
          "TGA" => 1,
          "TGC" => 1,
          "TGG" => 0,
          "TGT" => 2,
          "TTA" => 3,
          "TTC" => 2,
          "TTG" => 1,
          "TTT" => 6
        ];

        $seq_name = "aaa";

        $aNucleotids = [
            "T" => 26,
            "A" => 18,
            "C" => 9,
            "G" => 12
        ];

        $seq_len = 65;

        $n = 1;

        $oligo_len = "3";

        $aExpected = [
            "AAA" => [
                0 => 10,
                1 => 314,
                2 => 41,
                3 => 345,
            ],
            "AAC" => [
                0 => 10,
                1 => 186,
                2 => 41,
                3 => 217,
            ],
            "AAG" => [
                0 => 138,
                1 => 186,
                2 => 169,
                3 => 217,
            ],
            "AAT" => [
                0 => 138,
                1 => 314,
                2 => 169,
                3 => 345,
            ],
            "ACA" => [
                0 => 10,
                1 => 250,
                2 => 41,
                3 => 281,
            ],
            "ACC" => [
                0 => 10,
                1 => 122,
                2 => 41,
                3 => 153,
            ],
            "ACG" => [
                0 => 138,
                1 => 122,
                2 => 169,
                3 => 153,
            ],
            "ACT" => [
                0 => 138,
                1 => 250,
                2 => 169,
                3 => 281,
            ],
            "AGA" => [
                0 => 74,
                1 => 250,
                2 => 105,
                3 => 281,
            ],
            "AGC" => [
                0 => 74,
                1 => 122,
                2 => 105,
                3 => 153,
            ],
            "AGG" => [
                0 => 202,
                1 => 122,
                2 => 233,
                3 => 153,
            ],
            "AGT" => [
                0 => 202,
                1 => 250,
                2 => 233,
                3 => 281,
            ],
            "ATA" => [
                0 => 74,
                1 => 314,
                2 => 105,
                3 => 345,
            ],
            "ATC" => [
                0 => 74,
                1 => 186,
                2 => 105,
                3 => 217,
            ],
            "ATG" => [
                0 => 202,
                1 => 186,
                2 => 233,
                3 => 217,
            ],
            "ATT" => [
                0 => 202,
                1 => 314,
                2 => 233,
                3 => 345,
            ],
            "CAA" => [
                0 => 10,
                1 => 282,
                2 => 41,
                3 => 313,
            ],
            "CAC" => [
                0 => 10,
                1 => 154,
                2 => 41,
                3 => 185,
            ],
            "CAG" => [
                0 => 138,
                1 => 154,
                2 => 169,
                3 => 185,
            ],
            "CAT" => [
                0 => 138,
                1 => 282,
                2 => 169,
                3 => 313,
            ],
            "CCA" => [
                0 => 10,
                1 => 218,
                2 => 41,
                3 => 249,
            ],
            "CCC" => [
                0 => 10,
                1 => 90,
                2 => 41,
                3 => 121,
            ],
            "CCG" => [
                0 => 138,
                1 => 90,
                2 => 169,
                3 => 121,
            ],
            "CCT" => [
                0 => 138,
                1 => 218,
                2 => 169,
                3 => 249,
            ],
            "CGA" => [
                0 => 74,
                1 => 218,
                2 => 105,
                3 => 249,
            ],
            "CGC" => [
                0 => 74,
                1 => 90,
                2 => 105,
                3 => 121,
            ],
            "CGG" => [
                0 => 202,
                1 => 90,
                2 => 233,
                3 => 121,
            ],
            "CGT" => [
                0 => 202,
                1 => 218,
                2 => 233,
                3 => 249,
            ],
            "CTA" => [
                0 => 74,
                1 => 282,
                2 => 105,
                3 => 313,
            ],
            "CTC" => [
                0 => 74,
                1 => 154,
                2 => 105,
                3 => 185,
            ],
            "CTG" => [
                0 => 202,
                1 => 154,
                2 => 233,
                3 => 185,
            ],
            "CTT" => [
                0 => 202,
                1 => 282,
                2 => 233,
                3 => 313,
            ],
            "GAA" => [
                0 => 42,
                1 => 282,
                2 => 73,
                3 => 313,
            ],
            "GAC" => [
                0 => 42,
                1 => 154,
                2 => 73,
                3 => 185,
            ],
            "GAG" => [
                0 => 170,
                1 => 154,
                2 => 201,
                3 => 185,
            ],
            "GAT" => [
                0 => 170,
                1 => 282,
                2 => 201,
                3 => 313,
            ],
            "GCA" => [
                0 => 42,
                1 => 218,
                2 => 73,
                3 => 249,
            ],
            "GCC" => [
                0 => 42,
                1 => 90,
                2 => 73,
                3 => 121,
            ],
            "GCG" => [
                0 => 170,
                1 => 90,
                2 => 201,
                3 => 121,
            ],
            "GCT" => [
                0 => 170,
                1 => 218,
                2 => 201,
                3 => 249,
            ],
            "GGA" => [
                0 => 106,
                1 => 218,
                2 => 137,
                3 => 249,
            ],
            "GGC" => [
                0 => 106,
                1 => 90,
                2 => 137,
                3 => 121,
            ],
            "GGG" => [
                0 => 234,
                1 => 90,
                2 => 265,
                3 => 121,
            ],
            "GGT" => [
                0 => 234,
                1 => 218,
                2 => 265,
                3 => 249,
            ],
            "GTA" => [
                0 => 106,
                1 => 282,
                2 => 137,
                3 => 313,
            ],
            "GTC" => [
                0 => 106,
                1 => 154,
                2 => 137,
                3 => 185,
            ],
            "GTG" => [
                0 => 234,
                1 => 154,
                2 => 265,
                3 => 185,
            ],
            "GTT" => [
                0 => 234,
                1 => 282,
                2 => 265,
                3 => 313,
            ],
            "TAA" => [
                0 => 42,
                1 => 314,
                2 => 73,
                3 => 345,
            ],
            "TAC" => [
                0 => 42,
                1 => 186,
                2 => 73,
                3 => 217,
            ],
            "TAG" => [
                0 => 170,
                1 => 186,
                2 => 201,
                3 => 217,
            ],
            "TAT" => [
                0 => 170,
                1 => 314,
                2 => 201,
                3 => 345,
            ],
            "TCA" => [
                0 => 42,
                1 => 250,
                2 => 73,
                3 => 281,
            ],
            "TCC" => [
                0 => 42,
                1 => 122,
                2 => 73,
                3 => 153,
            ],
            "TCG" => [
                0 => 170,
                1 => 122,
                2 => 201,
                3 => 153,
            ],
            "TCT" => [
                0 => 170,
                1 => 250,
                2 => 201,
                3 => 281,
            ],
            "TGA" => [
                0 => 106,
                1 => 250,
                2 => 137,
                3 => 281,
            ],
            "TGC" => [
                0 => 106,
                1 => 122,
                2 => 137,
                3 => 153,
            ],
            "TGG" => [
                0 => 234,
                1 => 122,
                2 => 265,
                3 => 153,
            ],
            "TGT" => [
                0 => 234,
                1 => 250,
                2 => 265,
                3 => 281,
            ],
            "TTA" => [
                0 => 106,
                1 => 314,
                2 => 137,
                3 => 345,
            ],
            "TTC" => [
                0 => 106,
                1 => 186,
                2 => 137,
                3 => 217,
            ],
            "TTG" => [
                0 => 234,
                1 => 186,
                2 => 265,
                3 => 217,
            ],
            "TTT" => [
                0 => 234,
                1 => 314,
                2 => 265,
                3 => 345,
            ]
        ];

        $service = new ChaosGameRepresentationManager($this->aNucleotidGraph, $this->apiMock);
        $testFunction = $service->createFCGRImage($oligos, $seq_name, $aNucleotids, $seq_len, $n, $oligo_len);

        $this->assertEquals($aExpected, $testFunction);
    }
}
