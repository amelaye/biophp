<?php

namespace Tests\MinitoolsBundle\Service;

use PHPUnit\Framework\TestCase;
use MinitoolsBundle\Service\MicrosatelliteRepeatsFinderManager;

class MicrosatelliteRepeatsFinderManagerTest extends TestCase
{
    public function testFindMicrosatelliteRepeats()
    {
        $sSequence = "AACAATGCCATGATGATGATTATTACGACACAACAACACCGCGCTTGACGGCGGCGGATGGATGCCGCGATCAGACGTTCAACGCCCACGTAACGTAACGCAACGTAACCTAACGACACTGTTAACGGTACGAT";
        $iMinLength = 2;
        $iMaxLength = 6;
        $iMinRepeats = 3;
        $iMinLengthOMR = 6;
        $iMismatchesAllowed = 10;
        $aExpected = [
            0 => [
            "start_position" => 9,
            "length" => 3,
            "repeats" => 3,
            "sequence" => "ATGATGATG",
            ],
            1 => [
            "start_position" => 29,
            "length" => 3,
            "repeats" => 3,
            "sequence" => "ACAACAACA"
            ],
            2 => [
            "start_position" => 48,
            "length" => 3,
            "repeats" => 3,
            "sequence" => "CGGCGGCGG",
            ]
        ];

        $service = new MicrosatelliteRepeatsFinderManager();
        $testFunction = $service->findMicrosatelliteRepeats($sSequence, $iMinLength, $iMaxLength, $iMinRepeats, $iMinLengthOMR, $iMismatchesAllowed);

        $this->assertEquals($testFunction, $aExpected);
    }

    public function testFindMicrosatelliteRepeatsException()
    {
        $this->expectException(\Exception::class);
        $sSequence = [];
        $iMinLength = 0;
        $iMaxLength = 0;
        $iMinRepeats = 0;
        $iMinLengthOMR = 0;
        $iMismatchesAllowed = 0;

        $service = new MicrosatelliteRepeatsFinderManager();
        $service->findMicrosatelliteRepeats($sSequence, $iMinLength, $iMaxLength, $iMinRepeats, $iMinLengthOMR, $iMismatchesAllowed);
    }

    public function testIncludeN1()
    {
        $sPrimer = "AACAA";
        $iMinus = 0;
        $sExpected = ".ACAA|A.CAA|AA.AA|AAC.A|AACA.";

        $service = new MicrosatelliteRepeatsFinderManager();
        $testFunction = $service->includeN1($sPrimer, $iMinus);

        $this->assertEquals($testFunction, $sExpected);
    }

    public function testIncludeN1Exception()
    {
        $this->expectException(\Exception::class);
        $sPrimer = [];
        $iMinus = 0;

        $service = new MicrosatelliteRepeatsFinderManager();
        $service->includeN1($sPrimer, $iMinus);
    }

    public function testIncludeN1Plus()
    {
        $sPrimer = "AACAA";
        $iMinus = 0;
        $sExpected = "..CAA|.A.AA|.AC.A|.ACA.|A..AA|A.C.A|A.CA.|AA..A|AA.A.|AAC..";

        $service = new MicrosatelliteRepeatsFinderManager();
        $testFunction = $service->includeNPlus1($sPrimer, $iMinus);

        $this->assertEquals($testFunction, $sExpected);
    }

    public function testIncludeN1PlusException()
    {
        $this->expectException(\Exception::class);
        $sPrimer = [];
        $iMinus = 0;

        $service = new MicrosatelliteRepeatsFinderManager();
        $service->includeNPlus1($sPrimer, $iMinus);
    }
}