<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\VendorApi;
use GuzzleHttp;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VendorApiTest extends WebTestCase
{
    private $vendorsObjects;
    private $clientMock;
    private $serializerMock;

    public function setUp(): void
    {
        $vendorsObjects = [];

        require 'samples/Vendors.php';

        $this->vendorsObjects = $vendorsObjects;

        $aMembers = [];
        foreach ($vendorsObjects as $vendor) {
            $aMembers[] = [
                'id' => $vendor->getId(),
                'vendor' => $vendor->getVendor(),
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

    public function testGetVendors()
    {
        $apiVendors = new VendorApi($this->clientMock, $this->serializerMock);
        static::assertEquals($this->vendorsObjects, $apiVendors->getVendors());
    }

    public function testGetVendorsArray()
    {
        $apiVendors = new VendorApi($this->clientMock, $this->serializerMock);
        $aResult = $apiVendors::GetVendorsArray($apiVendors->getVendors());

        $this->assertEquals("F", $aResult["AanI"]);
        $this->assertEquals("FIKMNR", $aResult["AatII"]);
        $this->assertCount(count($this->vendorsObjects), $aResult);
    }
}
