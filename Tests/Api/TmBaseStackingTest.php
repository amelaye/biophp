<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\TmBaseStackingApi;
use GuzzleHttp;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TmBaseStackingTest extends WebTestCase
{
    public function setUp()
    {
        $aTemperatureObjects = [];

        require 'samples/TmBaseStacking.php';

        $this->aTemperatures = $aTemperatureObjects;
        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetTmBaseStackings()
    {
        $apiTemperatures= new TmBaseStackingApi($this->clientMock, $this->serializerMock);
        static::assertEquals($this->aTemperatures, $apiTemperatures->getTmBaseStackings());
    }

    public function testGetEnthropyValues()
    {
        $aExpectedTm = [
          "AA" => -22.2,
          "AC" => -22.4,
          "AG" => -21.0,
          "AT" => -20.4,
          "CA" => -22.7,
          "CC" => -19.9,
          "CG" => -27.2,
          "CT" => -21.0,
          "GA" => -22.2,
          "GC" => -24.4,
          "GG" => -19.9,
          "GT" => -22.4,
          "TA" => -21.3,
          "TC" => -22.2,
          "TG" => -22.7,
          "TT" => -22.2
        ];

        $apiTemperatures= new TmBaseStackingApi($this->clientMock, $this->serializerMock);
        $temperatures = $apiTemperatures::GetEnthropyValues($apiTemperatures->getTmBaseStackings());
        static::assertEquals($aExpectedTm, $temperatures);
    }

    public function testGetEnthalpyValues()
    {
        $aExpectedTm = [
          "AA" => -7.9,
          "AC" => -8.4,
          "AG" => -7.8,
          "AT" => -7.2,
          "CA" => -8.5,
          "CC" => -8.0,
          "CG" => -10.6,
          "CT" => -7.8,
          "GA" => -8.2,
          "GC" => -9.8,
          "GG" => -8.0,
          "GT" => -8.4,
          "TA" => -7.2,
          "TC" => -8.2,
          "TG" => -8.5,
          "TT" => -7.9
        ];

        $apiTemperatures= new TmBaseStackingApi($this->clientMock, $this->serializerMock);
        $temperatures = $apiTemperatures::GetEnthalpyValues($apiTemperatures->getTmBaseStackings());
        static::assertEquals($aExpectedTm, $temperatures);
    }
}