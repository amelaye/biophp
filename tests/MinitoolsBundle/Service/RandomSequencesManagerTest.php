<?php


namespace Tests\MinitoolsBundle\Service;


use MinitoolsBundle\Service\RandomSequencesManager;
use PHPUnit\Framework\TestCase;

class RandomSequencesManagerTest extends TestCase
{
    protected $aminos;

    protected $proteins;

    public function setUp()
    {
        $this->aminos = [
          "A" => "T",
          "T" => "A",
          "G" => "C",
          "C" => "G",
        ];

        $this->proteins = [
            "Alanine" => [
                1 => "A",
                3 => "Ala",
            ],
            "Aspartate_or_asparagine" => [
                1 => "B",
            ],
            "Cysteine" => [
                1 => "C",
                3 => "Cys",
            ],
            "Aspartic_acid" => [
                1 => "D",
                3 => "Asp",
            ],
            "Glutamic_acid" => [
                1 => "E",
                3 => "Glu",
            ],
            "Phenylalanine" => [
                1 => "F",
                3 => "Phe",
            ],
            "Glycine" => [
                1 => "G",
                3 => "Gly",
            ],
            "Histidine" => [
                1 => "H",
                3 => "His",
            ],
            "Isoleucine" => [
                1 => "I",
                3 => "Ile",
            ],
            "Lysine" => [
                1 => "K",
                3 => "Lys",
            ],
            "Leucine" => [
                1 => "L",
                3 => "Leu",
            ],
            "Methionine" => [
                1 => "M",
                3 => "Met",
            ],
            "Asparagine" => [
                1 => "N",
                3 => "Asn",
            ],
            "Proline" => [
                1 => "P",
                3 => "Pro",
            ],
            "Glutamine" => [
                1 => "Q",
                3 => "Gin",
            ],
            "Arginine" => [
                1 => "R",
                3 => "Arg",
            ],
            "Serine" => [
                1 => "S",
                3 => "Ser",
            ],
            "Threonine" => [
                1 => "T",
                3 => "Thr",
            ],
            "Selenocysteine" => [
                1 => "U",
                3 => "Sec",
            ],
            "Valine" => [
                1 => "V",
                3 => "Val",
            ],
            "Tryptophan" => [
                1 => "W",
                3 => "Trp",
            ],
            "Tyrosine" => [
                1 => "Y",
                3 => "Tyr",
            ],
            "Glutamate_or_glutamine" => [
                1 => "Z",
            ],
            "Any" => [
                1 => "X",
                3 => "XXX",
            ],
            "STOP" => [
                1 => "*",
                3 => "STP",
            ],
        ];
    }

    public function testRandomize2ndOption30Len()
    {
        $aElements = [
          "A" => 9.0,
          "C" => 6.0,
          "G" => 6.0,
          "T" => 9.0,
        ];

        $service = new RandomSequencesManager($this->aminos, $this->proteins);
        $testFunction = $service->randomize($aElements);

        $this->assertEquals(30, strlen($testFunction));
    }

    public function testRandomize3rdOption100Len()
    {
        $aElements = [
          "A" => 1.0,
          "C" => 2.0,
          "D" => 5.0,
          "E" => 7.0,
          "F" => 4.0,
          "G" => 7.0,
          "H" => 7.0,
          "I" => 7.0,
          "K" => 6.0,
          "L" => 9.0,
          "M" => 2.0,
          "N" => 4.0,
          "P" => 6.0,
          "Q" => 4.0,
          "R" => 5.0,
          "S" => 7.0,
          "T" => 6.0,
          "V" => 6.0,
          "W" => 2.0,
          "Y" => 3.0
        ];

        $service = new RandomSequencesManager($this->aminos, $this->proteins);
        $testFunction = $service->randomize($aElements);

        $this->assertEquals(100, strlen($testFunction));
    }

    public function testCreateFromSeqAminos()
    {
        $iLength = 100;

        $sSequence = 'GGCAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGT\r\n';
        $sSequence.= 'ATAAACAGTGCTGGAGGCTGGCGGGGCAGGCCAGCTGAGTCCTGAGCAGCAGCCCAGCGCAGCCACCGAGACACC\r\n';
        $sSequence.= 'ATGAGAGCCCTCACACTCCTCGCCCTATTGGCCCTGGCCGCACTTTGCATCGCTGGCCAGGCAGGTGAGTGCCCC\r\n';
        $sSequence.= 'CACCTCCCCTCAGGCCGCATTGCAGTGGGGGCTGAGAGGAGGAAGCACCATGGCCCACCTCTTCTCACCCCTTTG\r\n';
        $sSequence.= 'GCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAG';

        $service = new RandomSequencesManager($this->aminos, $this->proteins);
        $testFunction = $service->createFromSeq($iLength, $sSequence);

        $this->assertEquals(100, strlen($testFunction));
    }

    public function testCreateFromSeqProteins()
    {
        $iLength = 100;

        $sSequence = 'TVECRAQITESFTKRSKTVHHHLGGNNRTIKDKFVSMTGLWYYLLDPDESFGNEQLVGPHEIRQSILHIQ';
        $sSequence .= 'PMHSKIPFRNCPVLLKYGIHDPESVLGDETVECRAQITESFTKRSKTVHHHLGGNNRTIKDKFVSMTGLWYYLLDPDESFGNEQLVGPHEIRQSILHIQPMHSKIPFRNCPVLLKYGIHDPESVLGDE';

        $service = new RandomSequencesManager($this->aminos, $this->proteins);
        $testFunction = $service->createFromSeq($iLength, $sSequence);

        $this->assertEquals(99, strlen($testFunction));
    }

    public function testCreateFromACGT()
    {
        $aAminoAcids = [
          "A" => "29.5",
          "C" => "20.5",
          "G" => "20.5",
          "T" => "29.5"
        ];
        $iLength = 30;

        $service = new RandomSequencesManager($this->aminos, $this->proteins);
        $testFunction = $service->createFromACGT($aAminoAcids, $iLength);

        $this->assertEquals(30, strlen($testFunction));
    }

    public function testcreateFromAA()
    {
        $aAminoAcids = [
          "A" => "1.174",
          "C" => "2.395",
          "D" => "4.872",
          "E" => "6.662",
          "F" => "3.624",
          "G" => "7.532",
          "H" => "7.532",
          "I" => "7.532",
          "K" => "5.635",
          "L" => "9.412",
          "M" => "2.196",
          "N" => "3.789",
          "P" => "6.294",
          "Q" => "4.509",
          "R" => "5.607",
          "S" => "7.527",
          "T" => "5.685",
          "V" => "6.026",
          "W" => "1.48",
          "Y" => "2.84"
        ];

        $iLength = 100;

        $service = new RandomSequencesManager($this->aminos, $this->proteins);
        $testFunction = $service->createFromAA($aAminoAcids, $iLength);

        $this->assertEquals(99, strlen($testFunction));
    }
}