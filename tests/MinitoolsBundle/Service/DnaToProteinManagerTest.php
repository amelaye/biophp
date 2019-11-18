<?php
/**
 * Created by PhpStorm.
 * User: amelaye
 * Date: 2019-08-04
 * Time: 14:51
 */

namespace Tests\MinitoolsBundle\Service;

use MinitoolsBundle\Service\DnaToProteinManager;
use PHPUnit\Framework\TestCase;

class DnaToProteinManagerTest extends TestCase
{
    protected $apiMock;

    protected function setUp()
    {
        /**
         * Mock API
         */
        $aAminos = [
            "STOP" => [
                1 => "*",
                3 => "STP",
            ],
            "Alanine" => [
                1 => "A",
                3 => "Ala",
            ],
            "Aspartate or asparagine" => [
                1 => "B",
                3 => "N/A",
            ],
            "Cysteine" => [
                1 => "C",
                3 => "Cys",
            ],
            "Aspartic acid" => [
                1 => "D",
                3 => "Asp",
            ],
            "Glutamic acid" => [
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
            "Pyrrolysine" => [
                1 => "O",
                3 => "Pyr",
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
            "Any" => [
                1 => "X",
                3 => "XXX",
            ],
            "Tyrosine" => [
                1 => "Y",
                3 => "Tyr",
            ],
            "Glutamate or glutamine" => [
                1 => "Z",
                3 => "N/A",
            ]
        ];

        $tripletsGroups = [
            "standard" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG |TGA )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "vertebrate_mitochondrial" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG |AGA |AGG )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG |TGA )",
                19 => "(CG. )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "yeast_mitochondrial" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG )",
                2 => "(ATT |ATC )",
                3 => "(ATG |ATA )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. |CT. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG |TGA )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "mold_protozoan_coelenterate_mitochondrial" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG |TGA )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "invertebrate_mitochondrial" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC )",
                3 => "(ATG |ATA )",
                4 => "(GT. )",
                5 => "(TC. |AG. )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG |TGA )",
                19 => "(CG. )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "ciliate_dasycladacean_hexamita_nuclear" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TGA )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG |TAA |TAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "echinoderm_mitochondrial" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AG. )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAA |AAT |AAC )",
                14 => "(AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG |TGA )",
                19 => "(CG. )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "euplotid_nuclear" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC |TGA )",
                18 => "(TGG )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "bacterial_plant_plastid" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG |TGA )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "alternative_yeast_nuclear" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CTA |CTT |CTC )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC |CTG )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG |TGA )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "ascidian_mitochondria" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC )",
                3 => "(ATG |ATA )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG |TGA )",
                19 => "(CG. )",
                20 => "(GG. |AGA |AGG )",
                21 => "(\S\S\S )",
            ],
            "flatworm_mitochondrial" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AG. )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC |TAA )",
                10 => "(TAG )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC |AAA )",
                14 => "(AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG |TGA )",
                19 => "(CG. )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "blepharisma_macronuclear" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TGA )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG |TAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "chlorophycean_mitochondrial" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. |TAG )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TGA )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "trematode_mitochondrial" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. )",
                2 => "(ATT |ATC )",
                3 => "(ATG |ATA )",
                4 => "(GT. )",
                5 => "(TC. |AG. )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TAG )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC |AAA )",
                14 => "(AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG |TGA )",
                19 => "(CG. )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "scenedesmus_obliquus_mitochondrial" => [
                0 => "(TTT |TTC )",
                1 => "(TTA |TTG |CT. |TAG )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TCT |TCC |TCG |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TAA |TGA |TCA )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ],
            "thraustochytrium_mitochondrial_code" => [
                0 => "(TTT |TTC )",
                1 => "(TTG |CT. )",
                2 => "(ATT |ATC |ATA )",
                3 => "(ATG )",
                4 => "(GT. )",
                5 => "(TC. |AGT |AGC )",
                6 => "(CC. )",
                7 => "(AC. )",
                8 => "(GC. )",
                9 => "(TAT |TAC )",
                10 => "(TTA |TAA |TAG |TGA )",
                11 => "(CAT |CAC )",
                12 => "(CAA |CAG )",
                13 => "(AAT |AAC )",
                14 => "(AAA |AAG )",
                15 => "(GAT |GAC )",
                16 => "(GAA |GAG )",
                17 => "(TGT |TGC )",
                18 => "(TGG )",
                19 => "(CG. |AGA |AGG )",
                20 => "(GG. )",
                21 => "(\S\S\S )",
            ]
        ];

        $aTripletsList = [0 => "TTT ", 1 => "TTC ", 2 => "TTA ", 3 => "TTG ", 4 => "TCT ", 5 => "TCC ", 6 => "TCA ",
  7 => "TCG ", 8 => "TAT ", 9 => "TAC ", 10 => "TAA ", 11 => "TAG ", 12 => "TGT ", 13 => "TGC ", 14 => "TGA ",
  15 => "TGG ", 16 => "CTT ", 17 => "CTC ", 18 => "CTA ", 19 => "CTG ", 20 => "CCT ", 21 => "CCC ", 22 => "CCA ",
  23 => "CCG ", 24 => "CAT ", 25 => "CAC ", 26 => "CAA ", 27 => "CAG ", 28 => "CGT ", 29 => "CGC ", 30 => "CGA ",
  31 => "CGG ", 32 => "ATT ", 33 => "ATC ", 34 => "ATA ", 35 => "ATG ", 36 => "ACT ", 37 => "ACC ", 38 => "ACA ",
  39 => "ACG ", 40 => "AAT ", 41 => "AAC ", 42 => "AAA ", 43 => "AAG ", 44 => "AGT ", 45 => "AGC ", 46 => "AGA ",
  47 => "AGG ", 48 => "GTT ", 49 => "GTC ", 50 => "GTA ", 51 => "GTG ", 52 => "GCT ", 53 => "GCC ", 54 => "GCA ",
  55 => "GCG ", 56 => "GAT ", 57 => "GAC ", 58 => "GAA ", 59 => "GAG ", 60 => "GGT ", 61 => "GCG ", 62 => "GGA ",
  63 => "GGG "];

        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiMock = $this->getMockBuilder('AppBundle\Api\Bioapi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getAminosOnlyLetters','getTripletsGroups','getTripletsList'])
            ->getMock();

        $this->apiMock->method("getAminosOnlyLetters")->will($this->returnValue($aAminos));
        $this->apiMock->method("getTripletsGroups")->will($this->returnValue($tripletsGroups));
        $this->apiMock->method("getTripletsList")->will($this->returnValue($aTripletsList));
    }

    public function testCustomTreatmentOneFrame()
    {
        $iFrames = "1";

        $sSequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCC";
        $sSequence .= "GCCGAGGGACCGGTGGGCGACGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGCGGAGTGAGGGGAGCAGTTGGGCCAA";
        $sSequence .= "GATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTG";

        $sMycode = "FFLLSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG";

        $aFrames = [
            1 => "GVRGAVGPRWRPPRDRWATRE*GEQLXQDXXRRGTGGRRGSEGSSWAKMAAAEGPVXDXGVRGAVGPRWRPPRDRWATGSEGSSWAKMAAAEGPVXDAGV"
        ];

        $service = new DnaToProteinManager($this->apiMock);
        $testFunction = $service->customTreatment($iFrames, $sSequence, $sMycode);

        $this->assertEquals($aFrames, $testFunction);
    }

    public function testCustomTreatmentLess3Frames()
    {
        $iFrames = "3";

        $sSequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCC";
        $sSequence .= "GCCGAGGGACCGGTGGGCGACGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGCGGAGTGAGGGGAGCAGTTGGGCCAA";
        $sSequence .= "GATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTG";

        $sMycode = "FFLLSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG";

        $aFrames = [
          1 => "GVRGAVGPRWRPPRDRWATRE*GEQLXQDXXRRGTGGRRGSEGSSWAKMAAAEGPVXDXGVRGAVGPRWRPPRDRWATGSEGSSWAKMAAAEGPVXDAGV",
          2 => "E*GEQLXQDXXRRGTGGRRGSEGSSWAKMAAAEGPVXDGGVRGAVGPRWRPPRDRWATAE*GEQLXQDXXRRGTGGRRGVRGAVGPRWRPPRDRWATRE",
          3 => "SEGSSWAKMAAAEGPVXDAGVRGAVGPRWRPPRDRWATGE*GEQLXQDXXRRGTGGRRRSEGSSWAKMAAAEGPVXDGE*GEQLXQDXXRRGTGGRRGS",
        ];

        $service = new DnaToProteinManager($this->apiMock);
        $testFunction = $service->customTreatment($iFrames, $sSequence, $sMycode);

        $this->assertEquals($aFrames, $testFunction);
    }

    public function testCustomTreatmentMore3Frames()
    {
        $iFrames = "6";

        $sSequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCC";
        $sSequence .= "GCCGAGGGACCGGTGGGCGACGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGCGGAGTGAGGGGAGCAGTTGGGCCAA";
        $sSequence .= "GATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTG";

        $sMycode = "FFLLSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG";

        $aFrames = [
            1 => "GVRGAVGPRWRPPRDRWATRE*GEQLXQDXXRRGTGGRRGSEGSSWAKMAAAEGPVXDXGVRGAVGPRWRPPRDRWATGSEGSSWAKMAAAEGPVXDAGV",
            2 => "E*GEQLXQDXXRRGTGGRRGSEGSSWAKMAAAEGPVXDGGVRGAVGPRWRPPRDRWATAE*GEQLXQDXXRRGTGGRRGVRGAVGPRWRPPRDRWATRE",
            3 => "SEGSSWAKMAAAEGPVXDAGVRGAVGPRWRPPRDRWATGE*GEQLXQDXXRRGTGGRRRSEGSSWAKMAAAEGPVXDGE*GEQLXQDXXRRGTGGRRGS",
            4 => "PHSPRQPGSTAXXSLATRCALTPLVNPVLPPAAPWPPAAPSLPSSTRFYRRRLPXHPLPPHSPRQPGSTAXXSLATRCPSLPSSTRFYRRRLPXHPLRPH",
            5 => "LTPLVNPVLPPAAPWPPAAPSLPSSTRFYRRRLPXHPLPPHSPRQPGSTAXXSLATRCRLTPLVNPVLPPAAPWPPAAPHSPRQPGSTAXXSLATRCAL",
            6 => "SLPSSTRFYRRRLPXHPLRPHSPRQPGSTAXXSLATRCPLTPLVNPVLPPAAPWPPAAASLPSSTRFYRRRLPXHPLPLTPLVNPVLPPAAPWPPAAPS",
        ];

        $service = new DnaToProteinManager($this->apiMock);
        $testFunction = $service->customTreatment($iFrames, $sSequence, $sMycode);

        $this->assertEquals($aFrames, $testFunction);
    }

    public function testDefinedTreatmentOneFrame()
    {
        $iFrames = "1";

        $sSequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCC";
        $sSequence .= "GCCGAGGGACCGGTGGGCGACGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGCGGAGTGAGGGGAGCAGTTGGGCCAA";
        $sSequence .= "GATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTG";

        $sGeneticCode = "standard";

        $service = new DnaToProteinManager($this->apiMock);
        $testFunction = $service->definedTreatment($iFrames, $sGeneticCode, $sSequence);

        $aFrames = [
            1 => "GVRGAVGPRWRPPRDRWATRE*GEQLGQDGGRRGTGGRRGSEGSSWAKMAAAEGPVGDGGVRGAVGPRWRPPRDRWATGSEGSSWAKMAAAEGPVGDAGV"
        ];

        $this->assertEquals($aFrames, $testFunction);
    }

    public function testDefinedTreatmentLess3Frames()
    {
        $iFrames = "3";

        $sSequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCC";
        $sSequence .= "GCCGAGGGACCGGTGGGCGACGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGCGGAGTGAGGGGAGCAGTTGGGCCAA";
        $sSequence .= "GATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTG";

        $sGeneticCode = "yeast_mitochondrial";

        $service = new DnaToProteinManager($this->apiMock);
        $testFunction = $service->definedTreatment($iFrames, $sGeneticCode, $sSequence);

        $aFrames = [
          1 => "GVRGAVGPRWRPPRDRWATREWGEQLGQDGGRRGTGGRRGSEGSSWAKMAAAEGPVGDGGVRGAVGPRWRPPRDRWATGSEGSSWAKMAAAEGPVGDAGV",
          2 => "EWGEQLGQDGGRRGTGGRRGSEGSSWAKMAAAEGPVGDGGVRGAVGPRWRPPRDRWATAEWGEQLGQDGGRRGTGGRRGVRGAVGPRWRPPRDRWATRE",
          3 => "SEGSSWAKMAAAEGPVGDAGVRGAVGPRWRPPRDRWATGEWGEQLGQDGGRRGTGGRRRSEGSSWAKMAAAEGPVGDGEWGEQLGQDGGRRGTGGRRGS"
        ];

        $this->assertEquals($aFrames, $testFunction);
    }

    public function testDefinedTreatmentMore3Frames()
    {
        $iFrames = "6";

        $sSequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCC";
        $sSequence .= "GCCGAGGGACCGGTGGGCGACGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGCGGAGTGAGGGGAGCAGTTGGGCCAA";
        $sSequence .= "GATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTG";

        $sGeneticCode = "euplotid_nuclear";

        $service = new DnaToProteinManager($this->apiMock);
        $testFunction = $service->definedTreatment($iFrames, $sGeneticCode, $sSequence);

        $aFrames = [
          1 => "GVRGAVGPRWRPPRDRWATRECGEQLGQDGGRRGTGGRRGSEGSSWAKMAAAEGPVGDGGVRGAVGPRWRPPRDRWATGSEGSSWAKMAAAEGPVGDAGV",
          2 => "ECGEQLGQDGGRRGTGGRRGSEGSSWAKMAAAEGPVGDGGVRGAVGPRWRPPRDRWATAECGEQLGQDGGRRGTGGRRGVRGAVGPRWRPPRDRWATRE",
          3 => "SEGSSWAKMAAAEGPVGDAGVRGAVGPRWRPPRDRWATGECGEQLGQDGGRRGTGGRRRSEGSSWAKMAAAEGPVGDGECGEQLGQDGGRRGTGGRRGS",
          4 => "PHSPRQPGSTAGGSLATRCALTPLVNPVLPPAAPWPPAAPSLPSSTRFYRRRLPGHPLPPHSPRQPGSTAGGSLATRCPSLPSSTRFYRRRLPGHPLRPH",
          5 => "LTPLVNPVLPPAAPWPPAAPSLPSSTRFYRRRLPGHPLPPHSPRQPGSTAGGSLATRCRLTPLVNPVLPPAAPWPPAAPHSPRQPGSTAGGSLATRCAL",
          6 => "SLPSSTRFYRRRLPGHPLRPHSPRQPGSTAGGSLATRCPLTPLVNPVLPPAAPWPPAAASLPSSTRFYRRRLPGHPLPLTPLVNPVLPPAAPWPPAAPS",
        ];

        $this->assertEquals($aFrames, $testFunction);
    }

    public function testFindORF()
    {
        $sSequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCC";
        $sSequence .= "GCCGAGGGACCGGTGGGCGACGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGCGGAGTGAGGGGAGCAGTTGGGCCAA";
        $sSequence .= "GATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTG";

        $aFrames = [
          1 => "GVRGAVGPRWRPPRDRWATRE*GEQLXQDXXRRGTGGRRGSEGSSWAKMAAAEGPVXDXGVRGAVGPRWRPPRDRWATGSEGSSWAKMAAAEGPVXDAGV",
          2 => "E*GEQLXQDXXRRGTGGRRGSEGSSWAKMAAAEGPVXDGGVRGAVGPRWRPPRDRWATAE*GEQLXQDXXRRGTGGRRGVRGAVGPRWRPPRDRWATRE",
          3 => "SEGSSWAKMAAAEGPVXDAGVRGAVGPRWRPPRDRWATGE*GEQLXQDXXRRGTGGRRRSEGSSWAKMAAAEGPVXDGE*GEQLXQDXXRRGTGGRRGS"
        ];

        $iProtsize = "50";
        $bOnlyCoding = true;
        $bTrimmed = true;

        $aExpected = [
          1 => "_____________________*____x__xx_________________MAAAEGPVXDXGVRGAVGPRWRPPRDRWATGSEGSSWAKMAAAEGPVXDAGV",
          2 => "_*____x__xx_________________MAAAEGPVXDGGVRGAVGPRWRPPRDRWATAE*____x__xx_____________________________",
          3 => "________________x_______________________*____x__xx_________________________x___*____x__xx__________",
        ];

        $service = new DnaToProteinManager($this->apiMock);
        $testFunction = $service->findORF($aFrames, $iProtsize, $bOnlyCoding, $bTrimmed);

        $this->assertEquals($aExpected, $testFunction);
    }

    public function testFindORFException()
    {
        $this->expectException(\Exception::class);
        $aFrames = 4;

        $iProtsize = 100000;
        $bOnlyCoding = 6;
        $bTrimmed = 8;

        $service = new DnaToProteinManager($this->apiMock);
        $service->findORF($aFrames, $iProtsize, $bOnlyCoding, $bTrimmed);
    }

    public function testTranslateDNAToProtein()
    {
        $sSequence = "CCTCACTCCCCTCGTCAACCCGGTTCTACCGCCGGCGGCTCCCTGGCCACCCGCTGCGCCCTCACTCCCCTCGTCAACCCGGTTCTACCGCCGGCGGCTCCCTGGCCA";
        $sSequence .= "CCCGCTGCCCCCTCACTCCCCTCGTCAACCCGGTTCTACCGCCGGCGGCTCCCTGGCCACCCGCTGCCGCCTCACTCCCCTCGTCAACCCGGTTCTACCGCCGGCGGC";
        $sSequence .= "TCCCTGGCCACCCGCTGCCCCTCACTCCCCTCGTCAACCCGGTTCTACCGCCGGCGGCTCCCTGGCCACCCGCTGCGCCCTCAC";

        $sGeneticCode = "euplotid_nuclear";

        $sPeptide = "PHSPRQPGSTAGGSLATRCALTPLVNPVLPPAAPWPPAAPSLPSSTRFYRRRLPGHPLPPHSPRQPGSTAGGSLATRCPSLPSSTRFYRRRLPGHPLRPH";

        $service = new DnaToProteinManager($this->apiMock);
        $testFunction = $service->translateDNAToProtein($sSequence, $sGeneticCode);

        $this->assertEquals($sPeptide, $testFunction);
    }

    public function testTranslateDNAToProteinException()
    {
        $this->expectException(\Exception::class);
        $sSequence = 4;

        $sGeneticCode = "pim_poum";

        $service = new DnaToProteinManager($this->apiMock);
        $service->translateDNAToProtein($sSequence, $sGeneticCode);
    }
}