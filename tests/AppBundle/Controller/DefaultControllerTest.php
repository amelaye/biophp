<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DemoControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testIndex()
    {
        $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSequenceanalysis()
    {
        $this->client->request('GET', '/sequence-analysis');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testParseaseqdb()
    {
        $crawler = $this->client->request('GET', '/read-sequence-genbank');
        $response = $this->client->getResponse();

        //$crawler = $this->client->request('GET', '/minitools/chaos-game-representation/FCGR');
        //static::assertEquals(200, $this->client->getResponse()->getStatusCode());

        if (!$response->isSuccessful()) {
            $block = $crawler->filter('title');
            if ($block->count()) {
                $error = $block->text();
                dump($error);
            }
        }

        $this->assertEquals(200, $response->getStatusCode());
    }
}
