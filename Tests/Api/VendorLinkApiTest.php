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
            ["name" => "New England Biolabs", "url" => "http://www.neb.com"],
            $aResult["N"]
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
     * Every supplier code the enzymes refer to has a link, and every link is one REBASE lists : the
     * suppliers of v610 (2026) are B E I J K M N O Q R S V X. The older codes (A, C, F, H, P, U, Y,
     * and a different B and E) were dropped with the old snapshot of the enzymes they were attached to.
     */
    public function testEverySupplierCodeOfTheEnzymesHasALinkAndEveryLinkIsASupplierOfRebase()
    {
        require 'samples/Vendors.php';

        $aCodes = [];
        foreach ($vendors as $sCodes) {
            foreach (str_split($sCodes) as $sCode) {
                $aCodes[$sCode] = true;
            }
        }
        $aLinked = array_map(fn($oLink) => $oLink->getId(), $this->vendorLinksObjects);

        $this->assertSame([], array_values(array_diff(array_keys($aCodes), $aLinked)));
        $this->assertSame(str_split("BEIJKMNOQRSVX"), $aLinked);
    }

    /**
     * Rows read off REBASE v610's bairoch file (the CR line of each enzyme), not off the old snapshot.
     */
    public function testTheEnzymesCarryTheSuppliersOfRebase()
    {
        require 'samples/Vendors.php';

        $this->assertSame("BIJNQRSVX", $vendors["EcoRI"]);
        $this->assertSame("BIJNQRSVX", $vendors["HindIII"]);
        $this->assertSame("BJNQRSVX", $vendors["NotI"]);
        $this->assertSame("BJNQRVX", $vendors["XhoI"]);
        $this->assertSame("I", $vendors["AgsI"]);
        $this->assertCount(587, $vendors);
    }
}
