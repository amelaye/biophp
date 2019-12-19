<?php


namespace Tests\AppBundle\API;


use AppBundle\Api\AminoApi;
use AppBundle\Api\DTO\AminoDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp;

class AminoApiTest extends WebTestCase
{
    public function setUp()
    {
        $aAminosObjects = [];

        $amino = new AminoDTO();
        $amino->setId('*');
        $amino->setName("STOP");
        $amino->setName1Letter('*');
        $amino->setName3Letters('STP');
        $amino->setWeight1(0);
        $amino->setWeight2(0);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('A');
        $amino->setName("Alanine");
        $amino->setName1Letter('A');
        $amino->setName3Letters('Ala');
        $amino->setWeight1(89);
        $amino->setWeight2(89);
        $amino->setResidueMolWeight(71.07);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('B');
        $amino->setName("Aspartate or asparagine");
        $amino->setName1Letter('B');
        $amino->setName3Letters('N/A');
        $amino->setWeight1(132);
        $amino->setWeight2(132);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('C');
        $amino->setName("Cysteine");
        $amino->setName1Letter('C');
        $amino->setName3Letters('Cys');
        $amino->setWeight1(121);
        $amino->setWeight2(121);
        $amino->setResidueMolWeight(103.10);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('D');
        $amino->setName("Aspartic acid");
        $amino->setName1Letter('D');
        $amino->setName3Letters('Asp');
        $amino->setWeight1(133);
        $amino->setWeight2(133);
        $amino->setResidueMolWeight(115.08);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('E');
        $amino->setName("Glutamic acid");
        $amino->setName1Letter('E');
        $amino->setName3Letters('Glu');
        $amino->setWeight1(147);
        $amino->setWeight2(147);
        $amino->setResidueMolWeight(129.11);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('F');
        $amino->setName("Phenylalanine");
        $amino->setName1Letter('F');
        $amino->setName3Letters('Phe');
        $amino->setWeight1(165);
        $amino->setWeight2(165);
        $amino->setResidueMolWeight(147.17);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('G');
        $amino->setName("Glycine");
        $amino->setName1Letter('G');
        $amino->setName3Letters('Gly');
        $amino->setWeight1(75);
        $amino->setWeight2(75);
        $amino->setResidueMolWeight(57.05);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('H');
        $amino->setName("Histidine");
        $amino->setName1Letter('H');
        $amino->setName3Letters('His');
        $amino->setWeight1(155);
        $amino->setWeight2(155);
        $amino->setResidueMolWeight(137.14);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('I');
        $amino->setName("Isoleucine");
        $amino->setName1Letter('I');
        $amino->setName3Letters('Ile');
        $amino->setWeight1(131);
        $amino->setWeight2(131);
        $amino->setResidueMolWeight(113.15);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('K');
        $amino->setName("Lysine");
        $amino->setName1Letter('K');
        $amino->setName3Letters('Lys');
        $amino->setWeight1(146);
        $amino->setWeight2(146);
        $amino->setResidueMolWeight(128.17);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('L');
        $amino->setName("Leucine");
        $amino->setName1Letter('L');
        $amino->setName3Letters('Leu');
        $amino->setWeight1(131);
        $amino->setWeight2(131);
        $amino->setResidueMolWeight(113.15);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('M');
        $amino->setName("Methionine");
        $amino->setName1Letter('M');
        $amino->setName3Letters('Met');
        $amino->setWeight1(149);
        $amino->setWeight2(149);
        $amino->setResidueMolWeight(131.19);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('N');
        $amino->setName("Asparagine");
        $amino->setName1Letter('N');
        $amino->setName3Letters('Asn');
        $amino->setWeight1(132);
        $amino->setWeight2(132);
        $amino->setResidueMolWeight(114.08);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('O');
        $amino->setName("Pyrrolysine");
        $amino->setName1Letter('O');
        $amino->setName3Letters('Pyr');
        $amino->setWeight1(255);
        $amino->setWeight2(255);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('P');
        $amino->setName("Proline");
        $amino->setName1Letter('P');
        $amino->setName3Letters('Pro');
        $amino->setWeight1(115);
        $amino->setWeight2(115);
        $amino->setResidueMolWeight(97.11);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('Q');
        $amino->setName("Glutamine");
        $amino->setName1Letter('Q');
        $amino->setName3Letters('Gin');
        $amino->setWeight1(146);
        $amino->setWeight2(146);
        $amino->setResidueMolWeight(128.13);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('R');
        $amino->setName("Arginine");
        $amino->setName1Letter('R');
        $amino->setName3Letters('Arg');
        $amino->setWeight1(174);
        $amino->setWeight2(174);
        $amino->setResidueMolWeight(156.18);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('S');
        $amino->setName("Serine");
        $amino->setName1Letter('S');
        $amino->setName3Letters('Ser');
        $amino->setWeight1(105);
        $amino->setWeight2(105);
        $amino->setResidueMolWeight(87.07);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('T');
        $amino->setName("Threonine");
        $amino->setName1Letter('T');
        $amino->setName3Letters('Thr');
        $amino->setWeight1(119);
        $amino->setWeight2(119);
        $amino->setResidueMolWeight(101.10);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('U');
        $amino->setName("Selenocysteine");
        $amino->setName1Letter('U');
        $amino->setName3Letters('Sec');
        $amino->setWeight1(168);
        $amino->setWeight2(168);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('V');
        $amino->setName("Valine");
        $amino->setName1Letter('V');
        $amino->setName3Letters('Val');
        $amino->setWeight1(117);
        $amino->setWeight2(117);
        $amino->setResidueMolWeight(99.13);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('W');
        $amino->setName("Tryptophan");
        $amino->setName1Letter('W');
        $amino->setName3Letters('Trp');
        $amino->setWeight1(204);
        $amino->setWeight2(204);
        $amino->setResidueMolWeight(186.20);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('X');
        $amino->setName("Any");
        $amino->setName1Letter('X');
        $amino->setName3Letters('XXX');
        $amino->setWeight1(146);
        $amino->setWeight2(146);
        $amino->setResidueMolWeight(114.82);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('Y');
        $amino->setName("Tyrosine");
        $amino->setName1Letter('Y');
        $amino->setName3Letters('Tyr');
        $amino->setWeight1(181);
        $amino->setWeight2(181);
        $amino->setResidueMolWeight(163.17);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('Z');
        $amino->setName("Glutamate or glutamine");
        $amino->setName1Letter('Z');
        $amino->setName3Letters('N/A');
        $amino->setWeight1(75);
        $amino->setWeight2(204);
        $aAminosObjects[] = $amino;

        $this->aAminosObjects = $aAminosObjects;

        $aStream =  [
          "@context" => "/contexts/Amino",
          "@id" => "/aminos",
          "@type" => "hydra:Collection",
          "hydra:member" =>  [
            0 =>  [
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
            1 =>  [
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
            2 =>  [
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
            3 =>  [
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
            4 =>  [
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
            5 =>  [
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
            6 =>  [
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
            7 =>  [
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
            8 =>  [
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
            9 =>  [
              "@id" => "/aminos/I",
              "@type" => "Amino",
              "id" => "I",
              "name" => "Isoleucine",
              "name1Letter" => "I",
              "name3Letters" => "Ile",
              "weight1" => 131,
              "weight2" => 131,
              "residueMolWeight" => 113.15,
            ],
            10 =>  [
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
            11 =>  [
              "@id" => "/aminos/L",
              "@type" => "Amino",
              "id" => "L",
              "name" => "Leucine",
              "name1Letter" => "L",
              "name3Letters" => "Leu",
              "weight1" => 131,
              "weight2" => 131,
              "residueMolWeight" => 113.15,
            ],
            12 =>  [
              "@id" => "/aminos/M",
              "@type" => "Amino",
              "id" => "M",
              "name" => "Methionine",
              "name1Letter" => "M",
              "name3Letters" => "Met",
              "weight1" => 149,
              "weight2" => 149,
              "residueMolWeight" => 131.19,
            ],
            13 =>  [
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
            14 =>  [
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
            15 =>  [
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
            16 =>  [
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
            17 =>  [
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
            18 =>  [
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
            19 =>  [
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
            20 =>  [
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
            21 =>  [
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
            22 =>  [
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
            23 =>  [
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
            24 =>  [
              "@id" => "/aminos/Y",
              "@type" => "Amino",
              "id" => "Y",
              "name" => "Tyrosine",
              "name1Letter" => "Y",
              "name3Letters" => "Tyr",
              "weight1" => 181,
              "weight2" => 181,
              "residueMolWeight" => 163.17,
            ],
            25 =>  [
              "@id" => "/aminos/Z",
              "@type" => "Amino",
              "id" => "Z",
              "name" => "Glutamate or glutamine",
              "name1Letter" => "Z",
              "name3Letters" => "N/A",
              "weight1" => 75,
              "weight2" => 204,
              "residueMolWeight" => null,
            ]
          ],
          "hydra:totalItems" => 26
        ];

        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);

        $this->serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->setMethods(['deserialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->method('deserialize')->will($this->returnValue($aStream));

    }

    public function testGetAminos()
    {
        $apiAminos = new AminoApi($this->clientMock, $this->serializerMock);

        $this->assertEquals($this->aAminosObjects, $apiAminos->getAminos());
    }
}