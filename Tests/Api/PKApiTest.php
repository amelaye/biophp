<?php
namespace Tests\Api;

use GuzzleHttp;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Amelaye\BioPHP\Api\PKApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PKApiTest extends WebTestCase
{
    private $aPKObjects;
    private $clientMock;
    private $serializerMock;

    public function setUp(): void
    {
        $aPKObjects = [];

        require 'samples/PK.php';

        $this->aPKObjects = $aPKObjects;

        // getPkValueById() only ever fetches one entry per call; the mock
        // returns the "Solomon" entry (index 2), the one testGetPkValueById()
        // requests.
        $oPk = $aPKObjects[2];
        $aMember = [
            'id' => $oPk->getId(),
            'nTerminus' => $oPk->getNTerminus(),
            'k' => $oPk->getK(),
            'r' => $oPk->getR(),
            'h' => $oPk->getH(),
            'cTerminus' => $oPk->getCTerminus(),
            'd' => $oPk->getD(),
            'e' => $oPk->getE(),
            'c' => $oPk->getC(),
            'y' => $oPk->getY(),
        ];

        $oMockHandler = new MockHandler([
            new Response(200, [], json_encode($aMember)),
        ]);
        $this->clientMock = new GuzzleHttp\Client([
            'base_uri' => 'http://api.amelayes-biophp.net',
            'handler' => HandlerStack::create($oMockHandler),
        ]);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetPkValueById()
    {
        $pkElements = new PKApi($this->clientMock, $this->serializerMock);

        $oSolomon = $this->aPKObjects[2];
        $aExpected = [
            'ID' => $oSolomon->getId(),
            'NTERMINUS' => $oSolomon->getNTerminus(),
            'K' => $oSolomon->getK(),
            'R' => $oSolomon->getR(),
            'H' => $oSolomon->getH(),
            'CTERMINUS' => $oSolomon->getCTerminus(),
            'D' => $oSolomon->getD(),
            'E' => $oSolomon->getE(),
            'C' => $oSolomon->getC(),
            'Y' => $oSolomon->getY(),
        ];

        $this->assertEquals($aExpected, $pkElements->getPkValueById("Solomon"));
    }
}