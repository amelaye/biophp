<?php
namespace Tests\Api;


use Amelaye\BioPHP\Api\TypeIIbEndonucleaseApi;
use GuzzleHttp;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TypeIIbEndonucleaseTest extends WebTestCase
{
    public function setUp()
    {
        $aTypeIIbEndonucleases = [];

        require 'samples/Type2bEndonucleases.php';

        $this->aEnzymes = $aTypeIIbEndonucleases;
        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetTypeIIbEndonucleases()
    {
        $apiEnzymes = new TypeIIbEndonucleaseApi($this->clientMock, $this->serializerMock);
        static::assertEquals($this->aEnzymes, $apiEnzymes->getTypeIIbEndonucleases());
    }

    public function testGetTypeIIbEndonucleasesArray()
    {
        $aEnzymesExpected = [
          "AjuI#" =>  [
            0 => "AjuI",
            1 => "_NNNNN'NNNNNNNGAANNNNNNNTTGGNNNNNN_NNNNN_'",
            2 => "(............GAA.......TTGG...........|...........CCAA.......TTC............)",
            3 => 37,
            4 => 5,
            5 => -5,
            6 => 7,
          ],
          "AlfI#" =>  [
            0 => "AjuI",
            1 => "_NN'NNNNNNNNNNCGANNNNNNTGCNNNNNNNNNN_NN'",
            2 => "(............CGA......TGC............|............GCA......TCG............)",
            3 => 36,
            4 => 2,
            5 => -2,
            6 => 6,
          ],
          "AloI#" =>  [
            0 => "AloI",
            1 => "_NNNNN'NNNNNNNGAACNNNNNNTCCNNNNNNN_NNNNN'",
            2 => "(............GAAC......TCC............|............GGA......GTTC............)",
            3 => 37,
            4 => 5,
            5 => -5,
            6 => 7,
          ],
          "BaeI#" =>  [
            0 => "BaeI",
            1 => "_NNNNN'NNNNNNNNNNACNNNNGTAYCNNNNNNN_NNNNN'",
            2 => "(...............AC....GTACC............|...............AC....GTATC............|............GATAC....GT...............|............GGTAC....GT...............)",
            3 => 38,
            4 => 5,
            5 => -5,
            6 => 7,
          ],
          "BarI#" =>  [
            0 => "BarI",
            1 => "_NNNNN'NNNNNNNGAAGNNNNNNTACNNNNNNN_NNNNN'",
            2 => "(............GAAG......TAC............|............GTA......CTTC............)",
            3 => 37,
            4 => 5,
            5 => -5,
            6 => 7,
          ],
          "BcgI#" =>  [
            0 => "BcgI",
            1 => "_NN'NNNNNNNNNNCGANNNNNNTGCNNNNNNNNNN_NN'",
            2 => "(............CGA......TGC............|............GCA......TCG............)",
            3 => 36,
            4 => 2,
            5 => -2,
            6 => 6,
          ],
          "BdaI#" =>  [
            0 => "BdaI",
            1 => "_NN'NNNNNNNNNNTGANNNNNNTCANNNNNNNNNN_NN'",
            2 => "(............TGA......TCA............)",
            3 => 36,
            4 => 2,
            5 => -2,
            6 => 6,
          ],
          "BplI#" =>  [
            0 => "BplI",
            1 => "_NNNNN'NNNNNNNNGAGNNNNNCTCNNNNNNNN_NNNNN'",
            2 => "(.............GAG.....CTC.............|.............GAG.....CTC.............)",
            3 => 37,
            4 => 5,
            5 => -5,
            6 => 6,
          ],
          "BsaXI#" =>  [
            0 => "BsaXI",
            1 => "_NNN'NNNNNNNNNACNNNNNCTCCNNNNNNN_NNN'",
            2 => "(............AC.....CTCC..........|..........GGAG.....GT............)",
            3 => 33,
            4 => 3,
            5 => -3,
            6 => 6,
          ],
          "CspCI#" =>  [
            0 => "CspCI",
            1 => "_NN'NNNNNNNNNNNCAANNNNNGTGGNNNNNNNNNN_NN'",
            2 => "(.............CAA.....GTGG............|............GCA.....TCG.............)",
            3 => 37,
            4 => 2,
            5 => -2,
            6 => 7,
          ],
          "FalI#" =>  [
            0 => "FalI",
            1 => "_NNNNN'NNNNNNNNAAGNNNNNCTTNNNNNNNN_NNNNN'",
            2 => "(.............AAG.....CTT.............|.............AAG.....CTT.............)",
            3 => 37,
            4 => 5,
            5 => -5,
            6 => 6,
          ],
          "Hin4I#" =>  [
            0 => "Hin4I",
            1 => "_NNNNN'NNNNNNNNGAYNNNNNVTCNNNNNNNN_NNNNN'",
            2 => "(.............GAC.....ATC.............|.............GAC.....CTC.............|.............GAC.....GTC.............|.............GAT.....ATC.............|.............GAT.....CTC.............|.............GAT.....GTC.............|.............GAG.....ATC.............|.............GAG.....ATC.............)",
            3 => 37,
            4 => 5,
            5 => -5,
            6 => 6,
          ],
          "PpiI#" =>  [
            0 => "PpiI",
            1 => "_NNNNN'NNNNNNNGAACNNNNNCTCNNNNNNNN_NNNNN'",
            2 => "(............GAAC.....CTC.............|.............GAG.....GTTC............)",
            3 => 37,
            4 => 5,
            5 => -5,
            6 => 7,
          ],
          "PsrI#" =>  [
            0 => "PsrI",
            1 => "_NNNNN'NNNNNNNGAACNNNNNNTACNNNNNNN_NNNNN'",
            2 => "(............GAAC......TAC............|............GTA......GTTC............)",
            3 => 37,
            4 => 5,
            5 => -5,
            6 => 7,
          ],
          "TstI#" =>  [
            0 => "TstI",
            1 => "_NNNNN'NNNNNNNNCACNNNNNNTCCNNNNNNN_NNNNN'",
            2 => "(.............CAC......TCC............|............GGA......GTG.............)",
            3 => 37,
            4 => 5,
            5 => -5,
            6 => 6,
          ]
        ];

        $apiEnzymes = new TypeIIbEndonucleaseApi($this->clientMock, $this->serializerMock);
        static::assertEquals($aEnzymesExpected, $apiEnzymes::GetTypeIIbEndonucleasesArray($apiEnzymes->getTypeIIbEndonucleases()));
    }

}