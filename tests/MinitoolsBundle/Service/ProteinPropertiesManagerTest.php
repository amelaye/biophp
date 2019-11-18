<?php


namespace Tests\MinitoolsBundle\Service;


use MinitoolsBundle\Service\ProteinPropertiesManager;
use PHPUnit\Framework\TestCase;

class ProteinPropertiesManagerTest extends TestCase
{
    protected $aminos;

    protected $apiMock;

    public function setUp()
    {
        $this->aminos = [
            "*" => [
                "@id" => "/aminos/%252A",
                "@type" => "Amino",
                "id" => "*",
                "name" => "STOP",
                "name1Letter" => "*",
                "name3Letters" => "STP",
                "weight1" => 0,
                "weight2" => 0,
                "residueMolWeight" => null,
            ],
            "A" => [
                "@id" => "/aminos/A",
                "@type" => "Amino",
                "id" => "A",
                "name" => "Alanine",
                "name1Letter" => "A",
                "name3Letters" => "Ala",
                "weight1" => 89,
                "weight2" => 89,
                "residueMolWeight" => 71.07,
            ],
            "B" => [
                "@id" => "/aminos/B",
                "@type" => "Amino",
                "id" => "B",
                "name" => "Aspartate or asparagine",
                "name1Letter" => "B",
                "name3Letters" => "N/A",
                "weight1" => 132,
                "weight2" => 132,
                "residueMolWeight" => null,
            ],
            "C" => [
                "@id" => "/aminos/C",
                "@type" => "Amino",
                "id" => "C",
                "name" => "Cysteine",
                "name1Letter" => "C",
                "name3Letters" => "Cys",
                "weight1" => 121,
                "weight2" => 121,
                "residueMolWeight" => 103.1,
            ],
            "D" => [
                "@id" => "/aminos/D",
                "@type" => "Amino",
                "id" => "D",
                "name" => "Aspartic acid",
                "name1Letter" => "D",
                "name3Letters" => "Asp",
                "weight1" => 133,
                "weight2" => 133,
                "residueMolWeight" => 115.08,
            ],
            "E" => [
                "@id" => "/aminos/E",
                "@type" => "Amino",
                "id" => "E",
                "name" => "Glutamic acid",
                "name1Letter" => "E",
                "name3Letters" => "Glu",
                "weight1" => 147,
                "weight2" => 147,
                "residueMolWeight" => 129.11,
            ],
            "F" => [
                "@id" => "/aminos/F",
                "@type" => "Amino",
                "id" => "F",
                "name" => "Phenylalanine",
                "name1Letter" => "F",
                "name3Letters" => "Phe",
                "weight1" => 165,
                "weight2" => 165,
                "residueMolWeight" => 147.17,
            ],
            "G" => [
                "@id" => "/aminos/G",
                "@type" => "Amino",
                "id" => "G",
                "name" => "Glycine",
                "name1Letter" => "G",
                "name3Letters" => "Gly",
                "weight1" => 75,
                "weight2" => 75,
                "residueMolWeight" => 57.05,
            ],
            "H" => [
                "@id" => "/aminos/H",
                "@type" => "Amino",
                "id" => "H",
                "name" => "Histidine",
                "name1Letter" => "H",
                "name3Letters" => "His",
                "weight1" => 155,
                "weight2" => 155,
                "residueMolWeight" => 137.14,
            ],
            "I" => [
                "@id" => "/aminos/I",
                "@type" => "Amino",
                "id" => "I",
                "name" => "Isoleucine",
                "name1Letter" => "I",
                "name3Letters" => "Ile",
                "weight1" => 131,
                "weight2" => 131,
                "residueMolWeight" => 113.15
            ],
            "K" => [
                "@id" => "/aminos/K",
                "@type" => "Amino",
                "id" => "K",
                "name" => "Lysine",
                "name1Letter" => "K",
                "name3Letters" => "Lys",
                "weight1" => 146,
                "weight2" => 146,
                "residueMolWeight" => 128.17,
            ],
            "L" => [
                "@id" => "/aminos/L",
                "@type" => "Amino",
                "id" => "L",
                "name" => "Leucine",
                "name1Letter" => "L",
                "name3Letters" => "Leu",
                "weight1" => 131,
                "weight2" => 131,
                "residueMolWeight" => 113.15
            ],
            "M" => [
                "@id" => "/aminos/M",
                "@type" => "Amino",
                "id" => "M",
                "name" => "Methionine",
                "name1Letter" => "M",
                "name3Letters" => "Met",
                "weight1" => 149,
                "weight2" => 149,
                "residueMolWeight" => 131.19
            ],
            "N" => [
                "@id" => "/aminos/N",
                "@type" => "Amino",
                "id" => "N",
                "name" => "Asparagine",
                "name1Letter" => "N",
                "name3Letters" => "Asn",
                "weight1" => 132,
                "weight2" => 132,
                "residueMolWeight" => 114.08,
            ],
            "O" => [
                "@id" => "/aminos/O",
                "@type" => "Amino",
                "id" => "O",
                "name" => "Pyrrolysine",
                "name1Letter" => "O",
                "name3Letters" => "Pyr",
                "weight1" => 255,
                "weight2" => 255,
                "residueMolWeight" => null,
            ],
            "P" => [
                "@id" => "/aminos/P",
                "@type" => "Amino",
                "id" => "P",
                "name" => "Proline",
                "name1Letter" => "P",
                "name3Letters" => "Pro",
                "weight1" => 115,
                "weight2" => 115,
                "residueMolWeight" => 97.11,
            ],
            "Q" => [
                "@id" => "/aminos/Q",
                "@type" => "Amino",
                "id" => "Q",
                "name" => "Glutamine",
                "name1Letter" => "Q",
                "name3Letters" => "Gin",
                "weight1" => 146,
                "weight2" => 146,
                "residueMolWeight" => 128.13,
            ],
            "R" => [
                "@id" => "/aminos/R",
                "@type" => "Amino",
                "id" => "R",
                "name" => "Arginine",
                "name1Letter" => "R",
                "name3Letters" => "Arg",
                "weight1" => 174,
                "weight2" => 174,
                "residueMolWeight" => 156.18,
            ],
            "S" => [
                "@id" => "/aminos/S",
                "@type" => "Amino",
                "id" => "S",
                "name" => "Serine",
                "name1Letter" => "S",
                "name3Letters" => "Ser",
                "weight1" => 105,
                "weight2" => 105,
                "residueMolWeight" => 87.07,
            ],
            "T" => [
                "@id" => "/aminos/T",
                "@type" => "Amino",
                "id" => "T",
                "name" => "Threonine",
                "name1Letter" => "T",
                "name3Letters" => "Thr",
                "weight1" => 119,
                "weight2" => 119,
                "residueMolWeight" => 101.1,
            ],
            "U" => [
                "@id" => "/aminos/U",
                "@type" => "Amino",
                "id" => "U",
                "name" => "Selenocysteine",
                "name1Letter" => "U",
                "name3Letters" => "Sec",
                "weight1" => 168,
                "weight2" => 168,
                "residueMolWeight" => null,
            ],
            "V" => [
                "@id" => "/aminos/V",
                "@type" => "Amino",
                "id" => "V",
                "name" => "Valine",
                "name1Letter" => "V",
                "name3Letters" => "Val",
                "weight1" => 117,
                "weight2" => 117,
                "residueMolWeight" => 99.13,
            ],
            "W" => [
                "@id" => "/aminos/W",
                "@type" => "Amino",
                "id" => "W",
                "name" => "Tryptophan",
                "name1Letter" => "W",
                "name3Letters" => "Trp",
                "weight1" => 204,
                "weight2" => 204,
                "residueMolWeight" => 186.2,
            ],
            "X" => [
                "@id" => "/aminos/X",
                "@type" => "Amino",
                "id" => "X",
                "name" => "Any",
                "name1Letter" => "X",
                "name3Letters" => "XXX",
                "weight1" => 146,
                "weight2" => 146,
                "residueMolWeight" => 114.82,
            ],
            "Y" => [
                "@id" => "/aminos/Y",
                "@type" => "Amino",
                "id" => "Y",
                "name" => "Tyrosine",
                "name1Letter" => "Y",
                "name3Letters" => "Tyr",
                "weight1" => 181,
                "weight2" => 181,
                "residueMolWeight" => 163.17
            ],
            "Z" => [
                "@id" => "/aminos/Z",
                "@type" => "Amino",
                "id" => "Z",
                "name" => "Glutamate or glutamine",
                "name1Letter" => "Z",
                "name3Letters" => "N/A",
                "weight1" => 75,
                "weight2" => 204,
                "residueMolWeight" => null
            ]
        ];

        $pk = "EMBOSS";

        $aPK = [
            "@CONTEXT" => "/contexts/PK",
            "@ID" => "/p_ks/EMBOSS",
            "@TYPE" => "PK",
            "ID" => "EMBOSS",
            "NTERMINUS" => 8.6,
            "K" => 10.8,
            "R" => 12.5,
            "H" => 6.5,
            "CTERMINUS" => 3.6,
            "D" => 3.9,
            "E" => 4.1,
            "C" => 8.5,
            "Y" => 10.1
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
            ->setMethods(["getAminos", "getPkValueById"])
            ->getMock();
        $this->apiMock->method("getAminos")->will($this->returnValue($this->aminos));
        $this->apiMock->method("getPkValueById")->will($this->returnValue($aPK));
    }

    public function testConvertInto3lettersCode()
    {
        $subsequence = "RNDCEQGHILKMFPSTW";
        $sExpected = "ArgAsnAspCysGluGinGlyHisIleLeuLysMetPheProSerThrTrp";

        $service = new ProteinPropertiesManager($this->apiMock);
        $testFunction = $service->convertInto3lettersCode($subsequence);

        $this->assertEquals($sExpected, $testFunction);

    }

    public function testWriteSubsequence()
    {
        $iStart = 6;
        $iEnd = 17;
        $sSequence = "ARNDCEQGHILKMFPSTWYVX*";

        $sExpected = "EQGHILKMFPST";

        $service = new ProteinPropertiesManager($this->apiMock);
        $testFunction = $service->writeSubsequence($iStart, $iEnd, $sSequence);

        $this->assertEquals($sExpected, $testFunction);
    }

    public function testProteinIsoelectricPoint()
    {
        $aAminoacidContent = [
          "*" => 0,
          "A" => 0,
          "C" => 0,
          "D" => 0,
          "E" => 1,
          "F" => 1,
          "G" => 1,
          "H" => 1,
          "I" => 1,
          "K" => 1,
          "L" => 1,
          "M" => 1,
          "N" => 0,
          "O" => 0,
          "P" => 1,
          "Q" => 1,
          "R" => 0,
          "S" => 1,
          "T" => 1,
          "U" => 0,
          "V" => 0,
          "W" => 0,
          "X" => 0,
          "Y" => 0
        ];

        $fExpected = 7.55;

        $service = new ProteinPropertiesManager($this->apiMock);
        $service->setPk("EMBOSS");
        $testFunction = $service->proteinIsoelectricPoint($aAminoacidContent);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testPartialCharge()
    {
        $fVal1 = 8.6;
        $iVal2 = 7;
        $fExpected = 0.97549663244966;

        $service = new ProteinPropertiesManager($this->apiMock);
        $testFunction = $service->partialCharge($fVal1, $iVal2);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testProteinCharge()
    {
        $aAminoacidContent = [
          "*" => 0,
          "A" => 0,
          "C" => 0,
          "D" => 0,
          "E" => 1,
          "F" => 1,
          "G" => 1,
          "H" => 1,
          "I" => 1,
          "K" => 1,
          "L" => 1,
          "M" => 1,
          "N" => 0,
          "O" => 0,
          "P" => 1,
          "Q" => 1,
          "R" => 0,
          "S" => 1,
          "T" => 1,
          "U" => 0,
          "V" => 0,
          "W" => 0,
          "X" => 0,
          "Y" => 0,
        ];
        $iPH = 7;

        $fExpected = 0.217246532853;

        $service = new ProteinPropertiesManager($this->apiMock);
        $service->setPk("EMBOSS");
        $testFunction = $service->proteinCharge($aAminoacidContent, $iPH);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testFormatAminoacidContent()
    {
        $aAminoacidContent = [
            "*" => 0,
            "A" => 0,
            "C" => 0,
            "D" => 0,
            "E" => 1,
            "F" => 1,
            "G" => 1,
            "H" => 1,
            "I" => 1,
            "K" => 1,
            "L" => 1,
            "M" => 1,
            "N" => 0,
            "O" => 0,
            "P" => 1,
            "Q" => 1,
            "R" => 0,
            "S" => 1,
            "T" => 1,
            "U" => 0,
            "V" => 0,
            "W" => 0,
            "X" => 0,
            "Y" => 0,
        ];

        $aExpected = [
            0 =>  [
                "one_letter" => "*",
                "three_letters" => "STP",
                "count" => 0,
            ],
            1 => [
                "one_letter" => "A",
                "three_letters" => "Ala",
                "count" => 0,
            ],
            2 => [
                "one_letter" => "C",
                "three_letters" => "Cys",
                "count" => 0,
            ],
            3 => [
                "one_letter" => "D",
                "three_letters" => "Asp",
                "count" => 0,
            ],
            4 => [
                "one_letter" => "E",
                "three_letters" => "Glu",
                "count" => 1,
            ],
            5 => [
                "one_letter" => "F",
                "three_letters" => "Phe",
                "count" => 1,
            ],
            6 => [
                "one_letter" => "G",
                "three_letters" => "Gly",
                "count" => 1,
            ],
            7 => [
                "one_letter" => "H",
                "three_letters" => "His",
                "count" => 1,
            ],
            8 => [
                "one_letter" => "I",
                "three_letters" => "Ile",
                "count" => 1,
            ],
            9 => [
                "one_letter" => "K",
                "three_letters" => "Lys",
                "count" => 1,
            ],
            10 => [
                "one_letter" => "L",
                "three_letters" => "Leu",
                "count" => 1,
            ],
            11 => [
                "one_letter" => "M",
                "three_letters" => "Met",
                "count" => 1,
            ],
            12 => [
                "one_letter" => "N",
                "three_letters" => "Asn",
                "count" => 0,
            ],
            13 => [
                "one_letter" => "O",
                "three_letters" => "Pyr",
                "count" => 0,
            ],
            14 => [
                "one_letter" => "P",
                "three_letters" => "Pro",
                "count" => 1,
            ],
            15 => [
                "one_letter" => "Q",
                "three_letters" => "Gin",
                "count" => 1,
            ],
            16 => [
                "one_letter" => "R",
                "three_letters" => "Arg",
                "count" => 0,
            ],
            17 => [
                "one_letter" => "S",
                "three_letters" => "Ser",
                "count" => 1,
            ],
            18 => [
                "one_letter" => "T",
                "three_letters" => "Thr",
                "count" => 1,
            ],
            19 => [
                "one_letter" => "U",
                "three_letters" => "Sec",
                "count" => 0,
            ],
            20 => [
                "one_letter" => "V",
                "three_letters" => "Val",
                "count" => 0,
            ],
            21 => [
                "one_letter" => "W",
                "three_letters" => "Trp",
                "count" => 0,
            ],
            22 => [
                "one_letter" => "X",
                "three_letters" => "XXX",
                "count" => 0,
            ],
            23 => [
                "one_letter" => "Y",
                "three_letters" => "Tyr",
                "count" => 0,
            ],
        ];

        $service = new ProteinPropertiesManager($this->apiMock);
        $testFunction = $service->formatAminoacidContent($aAminoacidContent);

        $this->assertEquals($aExpected, $testFunction);
    }

    public function testAminoacidContent()
    {
        $seq = "EQGHILKMFPST";

        $aExpected = [
          "*" => 0,
          "A" => 0,
          "C" => 0,
          "D" => 0,
          "E" => 1,
          "F" => 1,
          "G" => 1,
          "H" => 1,
          "I" => 1,
          "K" => 1,
          "L" => 1,
          "M" => 1,
          "N" => 0,
          "O" => 0,
          "P" => 1,
          "Q" => 1,
          "R" => 0,
          "S" => 1,
          "T" => 1,
          "U" => 0,
          "V" => 0,
          "W" => 0,
          "X" => 0,
          "Y" => 0
        ];

        $service = new ProteinPropertiesManager($this->apiMock);
        $testFunction = $service->aminoacidContent($seq);

        $this->assertEquals($aExpected, $testFunction);
    }

    public function testMolarAbsorptionCoefficientOfProt()
    {
        $aminoacid_content = [
          "*" => 0,
          "A" => 1,
          "C" => 1,
          "D" => 1,
          "E" => 1,
          "F" => 1,
          "G" => 1,
          "H" => 1,
          "I" => 1,
          "K" => 1,
          "L" => 1,
          "M" => 1,
          "N" => 1,
          "O" => 0,
          "P" => 1,
          "Q" => 1,
          "R" => 1,
          "S" => 1,
          "T" => 1,
          "U" => 0,
          "V" => 0,
          "W" => 0,
          "X" => 0,
          "Y" => 0,
        ];

        $molweight = 1947.07;
        $fExpected = 2.8889562265353;

        $service = new ProteinPropertiesManager($this->apiMock);
        $testFunction = $service->molarAbsorptionCoefficientOfProt($aminoacid_content, $molweight);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testProteinMolecularWeight()
    {
        $aminoacid_content = [
            "*" => 0,
            "A" => 1,
            "C" => 1,
            "D" => 1,
            "E" => 1,
            "F" => 1,
            "G" => 1,
            "H" => 1,
            "I" => 1,
            "K" => 1,
            "L" => 1,
            "M" => 1,
            "N" => 1,
            "O" => 0,
            "P" => 1,
            "Q" => 1,
            "R" => 1,
            "S" => 1,
            "T" => 1,
            "U" => 0,
            "V" => 0,
            "W" => 0,
            "X" => 0,
            "Y" => 0,
        ];

        $fExpected = 1947.07;

        $service = new ProteinPropertiesManager($this->apiMock);
        $testFunction = $service->proteinMolecularWeight($aminoacid_content);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testProteinAminoacidNature1()
    {
        $sSequence = "ARNDCEQGHILKMFPST";
        $aColors = [
            "polar" => "magenta",
            "nonpolar" => "yellow",
            "charged" => "red",
            "hydrophobic" => "green",
            "positively_charged" => "blue",
            "negatively_charged" => "red"
        ];

        $aExpected = [
            0 => [
                0 => "A",
                1 => "yellow",
            ],
            1 => [
                0 => "R",
                1 => "red",
            ],
            2 => [
                0 => "N",
                1 => "magenta",
            ],
            3 => [
                0 => "D",
                1 => "red",
            ],
            4 => [
                0 => "C",
                1 => "magenta",
            ],
            5 => [
                0 => "E",
                1 => "red",
            ],
            6 => [
                0 => "Q",
                1 => "magenta",
            ],
            7 => [
                0 => "G",
                1 => "yellow",
            ],
            8 => [
                0 => "H",
                1 => "magenta",
            ],
            9 => [
                0 => "I",
                1 => "yellow",
            ],
            10 => [
                0 => "L",
                1 => "yellow",
            ],
            11 => [
                0 => "K",
                1 => "red",
            ],
            12 => [
                0 => "M",
                1 => "yellow",
            ],
            13 => [
                0 => "F",
                1 => "yellow",
            ],
            14 => [
                0 => "P",
                1 => "yellow",
            ],
            15 => [
                0 => "S",
                1 => "magenta",
            ],
            16 => [
                0 => "T",
                1 => "magenta"
            ]
        ];

        $service = new ProteinPropertiesManager($this->apiMock);
        $testFunction = $service->proteinAminoacidNature1($sSequence, $aColors);

        $this->assertEquals($aExpected, $testFunction);
    }

    public function testProteinAminoacidNature2()
    {
        $sSequence = "ARNDCEQGHILKMFP";
        $aColors = [
            "polar" => "magenta",
            "nonpolar" => "yellow",
            "charged" => "red",
            "hydrophobic" => "green",
            "positively_charged" => "blue",
            "negatively_charged" => "red"
        ];

        $aExpected = [
            0 => [
                0 => "A",
                1 => "yellow",
            ],
            1 => [
                0 => "R",
                1 => "blue",
            ],
            2 => [
                0 => "N",
                1 => "red",
            ],
            3 => [
                0 => "D",
                1 => "magenta",
            ],
            4 => [
                0 => "C",
                1 => "green",
            ],
            5 => [
                0 => "E",
                1 => "red",
            ],
            6 => [
                0 => "Q",
                1 => "magenta",
            ],
            7 => [
                0 => "G",
                1 => "yellow",
            ],
            8 => [
                0 => "H",
                1 => "magenta",
            ],
            9 => [
                0 => "I",
                1 => "green",
            ],
            10 => [
                0 => "L",
                1 => "green",
            ],
            11 => [
                0 => "K",
                1 => "blue",
            ],
            12 => [
                0 => "M",
                1 => "green",
            ],
            13 => [
                0 => "F",
                1 => "green",
            ],
            14 => [
                0 => "P",
                1 => "green",
            ]
        ];

        $service = new ProteinPropertiesManager($this->apiMock);
        $testFunction = $service->proteinAminoacidNature2($sSequence, $aColors);

        $this->assertEquals($aExpected, $testFunction);
    }
}