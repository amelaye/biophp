<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\DTO\ElementDTO;
use Amelaye\BioPHP\Api\ElementApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ElementApiTest extends WebTestCase
{
    private $aElementsObjects;
    private $clientMock;
    private $serializerMock;

    public function setUp(): void
    {
        $aElementsObjects = [];

        require 'samples/Elements.php';

        $this->aElementsObjects = $aElementsObjects;

        $aMembers = [];
        foreach ($aElementsObjects as $element) {
            $aMembers[] = [
                'name' => $element->getName(),
                'weight' => $element->getWeight(),
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

    public function testGetElements()
    {
        $apiElements = new ElementApi($this->clientMock, $this->serializerMock);

        static::assertEquals($this->aElementsObjects, $apiElements->getElements());
    }
    /**
     * Unlike getElements(), which deserializes to a plain array and hydrates the DTO
     * by hand, getElement() deserializes straight into ElementDTO and therefore needs
     * JMS property metadata. This test exercises that path with a real serializer, so
     * it fails if the #[Type] attributes on ElementDTO ever go missing again.
     */
    public function testGetElement()
    {
        $oMockHandler = new MockHandler([
            new Response(200, [], json_encode(['id' => 6, 'name' => 'water', 'weight' => 18.015])),
        ]);
        $clientMock = new GuzzleHttp\Client([
            'base_uri' => 'http://api.amelayes-biophp.net',
            'handler' => HandlerStack::create($oMockHandler),
        ]);

        $elementExpected = new ElementDTO();
        $elementExpected->setId(6);
        $elementExpected->setName("water");
        $elementExpected->setWeight(18.015);

        $elementApi = new ElementApi($clientMock, $this->serializerMock);

        static::assertEquals($elementExpected, $elementApi->getElement(6));
    }
}