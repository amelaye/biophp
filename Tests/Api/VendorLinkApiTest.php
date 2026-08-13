<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\VendorLinkApi;
use GuzzleHttp;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VendorLinkApiTest extends WebTestCase
{
    private $vendorLinksObjects;
    private $clientMock;
    private $serializerMock;

    public function setUp(): void
    {
        $vendorLinksObjects = [];

        require 'samples/VendorLinks.php';

        $this->vendorLinksObjects = $vendorLinksObjects;

        $aMembers = [];
        foreach ($vendorLinksObjects as $vendorLink) {
            $aMembers[] = [
                'id' => $vendorLink->getId(),
                'name' => $vendorLink->getName(),
                'link' => $vendorLink->getLink(),
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

    public function testGetVendorLinks()
    {
        $apiVendorLinks = new VendorLinkApi($this->clientMock, $this->serializerMock);
        static::assertEquals($this->vendorLinksObjects, $apiVendorLinks->getVendorLinks());
    }

    public function testGetVendorLinksArray()
    {
        $apiVendorLinks = new VendorLinkApi($this->clientMock, $this->serializerMock);
        $aResult = $apiVendorLinks::GetVendorLinksArray($apiVendorLinks->getVendorLinks());

        $this->assertEquals(
            ["name" => "Minotech Biotechnology", "url" => "http://www.minotech.gr"],
            $aResult["C"]
        );
        $this->assertCount(count($this->vendorLinksObjects), $aResult);
    }

    /**
     * biotools' RestrictionDigestManager::showVendors() walks this array in order,
     * matching each key as a substring of a vendor-code string - it relies on
     * GetVendorLinksArray() preserving the input list's order, keyed by getId().
     */
    public function testGetVendorLinksArrayPreservesInputOrder()
    {
        $apiVendorLinks = new VendorLinkApi($this->clientMock, $this->serializerMock);
        $aResult = $apiVendorLinks::GetVendorLinksArray($apiVendorLinks->getVendorLinks());

        $aExpectedOrder = array_map(function ($vendorLink) {
            return $vendorLink->getId();
        }, $this->vendorLinksObjects);

        $this->assertEquals($aExpectedOrder, array_keys($aResult));
    }
}
