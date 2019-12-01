<?php


namespace Tests\AppBundle\Bioapi;


use AppBundle\Api\Bioapi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use JMS\Serializer\Serializer;
use GuzzleHttp;

class BioapiTest extends WebTestCase
{
    public $http;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net/']);
    }

    public function testGetNucleotidsDNA()
    {
        $response = $this->http->request('GET', 'nucleotids');
        $this->assertEquals(200, $response->getStatusCode());
    }
}