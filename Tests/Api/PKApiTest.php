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
    public function setUp(): void
    {
        $aPKObjects = [];

        require 'samples/PK.php';

        $this->aPKObjects = $aPKObjects;

        $oPk = $aPKObjects[0];
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

    /*public function testGetElements()
    {
        $pkElements = new PKApi($this->clientMock, $this->serializerMock);

        $this->assertEquals((array)$this->aPKObjects[2], $pkElements->getPkValueById("Solomon"));
    }*/
}