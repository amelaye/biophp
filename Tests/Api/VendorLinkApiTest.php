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
            'base_uri' => 'https://api.amelayes-biophp.net',
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

    /**
     * Every supplier code the enzymes refer to has a link, but Y : REBASE no longer lists it (the
     * current supplier list, v610, is B E I J K M N O Q R S V X), so its name and address cannot
     * be sourced.
     */
    public function testEverySupplierCodeOfTheEnzymesHasALink()
    {
        require 'samples/Vendors.php';

        $aCodes = [];
        foreach ($vendors as $sCodes) {
            foreach (str_split($sCodes) as $sCode) {
                $aCodes[$sCode] = true;
            }
        }
        $aLinked = array_map(fn($oLink) => $oLink->getId(), $this->vendorLinksObjects);

        $this->assertSame(["Y"], array_values(array_diff(array_keys($aCodes), $aLinked)));
    }
}
