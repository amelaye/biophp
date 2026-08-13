<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\TypeIIsEndonucleaseApi;
use GuzzleHttp;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TypeIIsEndonucleaseTest extends WebTestCase
{
    private $aEnzymes;
    private $clientMock;
    private $serializerMock;

    public function setUp(): void
    {
        $aTypeIIsEndonucleases = [];

        require 'samples/Type2sEndonucleases.php';

        $this->aEnzymes = $aTypeIIsEndonucleases;

        $aMembers = [];
        foreach ($aTypeIIsEndonucleases as $endonuclease) {
            $aMembers[] = [
                'id' => $endonuclease->getId(),
                'samePattern' => $endonuclease->getSamePattern(),
                'recognitionPattern' => $endonuclease->getRecognitionPattern(),
                'computingPattern' => $endonuclease->getComputingPattern(),
                'lengthRecognitionPattern' => $endonuclease->getLengthRecognitionPattern(),
                'cleavagePosUpper' => $endonuclease->getCleavagePosUpper(),
                'cleavagePosLower' => $endonuclease->getCleavagePosLower(),
                'nbNonNBases' => $endonuclease->getNbNonNBases(),
            ];
        }

        $oMockHandler = new MockHandler([
            new Response(200, [], json_encode(['hydra:member' => $aMembers])),
        ]);
        $this->clientMock = new GuzzleHttp\Client([
            'base_uri' => 'http://api.amelayes-biophp.net',
            'handler' => HandlerStack::create($oMockHandler),
        ]);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetTypeIIsEndonucleases()
    {
        $apiEnzymes = new TypeIIsEndonucleaseApi($this->clientMock, $this->serializerMock);
        static::assertEquals($this->aEnzymes, $apiEnzymes->getTypeIIsEndonucleases());
    }

    public function testGetTypeIIsEndonucleasesArray()
    {
        $apiEnzymes = new TypeIIsEndonucleaseApi($this->clientMock, $this->serializerMock);
        $aResult = $apiEnzymes::GetTypeIIsEndonucleasesArray($apiEnzymes->getTypeIIsEndonucleases());

        $this->assertArrayHasKey("AarI", $aResult);
        $this->assertEquals(
            ["AarI", "CACCTGCNNNN'NNNN_", "(CACCTGC........)", 15, 11, 4, 7],
            $aResult["AarI"]
        );
    }
}
