<?php


namespace Tests\AppBundle\Service;


use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\DTO\AminoDTO;
use Amelaye\BioPHP\Api\Interfaces\AminoApiAdapter;
use Amelaye\BioPHP\Domain\Sequence\Entity\Protein;
use Amelaye\BioPHP\Domain\Sequence\Service\ProteinManager;
use PHPUnit\Framework\TestCase;

class ProteinManagerTest extends TestCase
{
    private $apiAminoMock;

    public function setUp()
    {
        $aWTS = [
          "*" =>  [
            0 => 0,
            1 => 0,
          ],
          "A" =>  [
            0 => 89,
            1 => 89,
          ],
          "B" =>  [
            0 => 132,
            1 => 132,
          ],
          "C" =>  [
            0 => 121,
            1 => 121,
          ],
          "D" =>  [
            0 => 133,
            1 => 133,
          ],
          "E" =>  [
            0 => 147,
            1 => 147,
          ],
          "F" =>  [
            0 => 165,
            1 => 165,
          ],
          "G" =>  [
            0 => 75,
            1 => 75,
          ],
          "H" =>  [
            0 => 155,
            1 => 155,
          ],
          "I" =>  [
            0 => 131,
            1 => 131,
          ],
          "K" =>  [
            0 => 146,
            1 => 146,
          ],
          "L" =>  [
            0 => 131,
            1 => 131,
          ],
          "M" =>  [
            0 => 149,
            1 => 149,
          ],
          "N" =>  [
            0 => 132,
            1 => 132,
          ],
          "O" =>  [
            0 => 255,
            1 => 255,
          ],
          "P" =>  [
            0 => 115,
            1 => 115,
          ],
          "Q" =>  [
            0 => 146,
            1 => 146,
          ],
          "R" =>  [
            0 => 174,
            1 => 174,
          ],
          "S" =>  [
            0 => 105,
            1 => 105,
          ],
          "T" =>  [
            0 => 119,
            1 => 119,
          ],
          "U" =>  [
            0 => 168,
            1 => 168,
          ],
          "V" =>  [
            0 => 117,
            1 => 117,
          ],
          "W" =>  [
            0 => 204,
            1 => 204,
          ],
          "X" =>  [
            0 => 146,
            1 => 146,
          ],
          "Y" =>  [
            0 => 181,
            1 => 181,
          ],
          "Z" =>  [
            0 => 75,
            1 => 204,
          ]
        ];

        
        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $aAminosObjects = [];
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

        $amino = new AminoDTO();
        $amino->setId('X');
        $amino->setName("Any");
        $amino->setName1Letter('X');
        $amino->setName3Letters('XXX');
        $amino->setWeight1(146);
        $amino->setWeight2(146);
        $amino->setResidueMolWeight(114.822);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('*');
        $amino->setName("STOP");
        $amino->setName1Letter('*');
        $amino->setName3Letters('STP');
        $amino->setWeight1(0);
        $amino->setWeight2(0);
        $aAminosObjects[] = $amino;

        $this->apiAminoMock = $this->getMockBuilder(AminoApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->getMock();

       /* $this->apiAminoMock = $this->getMock('AppBundle\Api\AminoApi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getAminos'])
            ->getMock();*/
       // $this->apiAminoMock->method("getAminos")->will($this->returnValue($aAminosObjects));
    }

    public function testSeqlen()
    {
        $proteinManager = new ProteinManager($this->apiAminoMock);

        $sProtein = "ARNDCEQGHARNDCEQGHILKMFPSTWYVXARNDKMFPSTWYVXARNDKMFPSTWYVXARNDCEQGHARNDCEQGHHARNDCEQGHILKMFPSTW";
        $sProtein .= "YVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWY";
        $sProtein .= "VXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPST";

        $oProtein = new Protein();
        $oProtein->setName("toto");
        $oProtein->setSequence($sProtein);
        $proteinManager->setProtein($oProtein);

        $len = $proteinManager->seqlen();
        $sExpected = 236;

        $this->assertEquals($sExpected, $len);
    }

    public function testMolwt()
    {
        $proteinManager = new ProteinManager($this->apiAminoMock);

        $sProtein = "ARNDCEQGHARNDCEQGHILKMFPSTWYVXARNDKMFPSTWYVXARNDKMFPSTWYVXARNDCEQGHARNDCEQGHHARNDCEQGHILKMFPSTW";
        $sProtein .= "YVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWY";
        $sProtein .= "VXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPST";

        $oProtein = new Protein();
        $oProtein->setName("toto");
        $oProtein->setSequence($sProtein);
        $proteinManager->setProtein($oProtein);

        $molwt = $proteinManager->molwt();
        $aExpected = [
          0 => 27928.475,
          1 => 27928.475
        ];

        $this->assertEquals($aExpected, $molwt);
    }
}