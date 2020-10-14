<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\TmBaseStackingApi;
use Amelaye\BioPHP\Api\TripletApi;
use GuzzleHttp;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TripletApiTest extends WebTestCase
{
    public function setUp()
    {
        $aTripletObjects = [];

        require 'samples/Triplets.php';

        $this->aTriplets = $aTripletObjects;
        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetTriplets()
    {
        $apiTriplets = new TripletApi($this->clientMock, $this->serializerMock);
        static::assertEquals($this->aTriplets, $apiTriplets->getTriplets());
    }

    public function testGetTripletsArray()
    {
        $aTripletsExpected = [
          0 => "TTT ",
          1 => "TTC ",
          2 => "TTA ",
          3 => "TTG ",
          4 => "TCT ",
          5 => "TCC ",
          6 => "TCA ",
          7 => "TCG ",
          8 => "TAT ",
          9 => "TAC ",
          10 => "TAA ",
          11 => "TAG ",
          12 => "TGT ",
          13 => "TGC ",
          14 => "TGA ",
          15 => "TGG ",
          16 => "CTT ",
          17 => "CTC ",
          18 => "CTA ",
          19 => "CTG ",
          20 => "CCT ",
          21 => "CCC ",
          22 => "CCA ",
          23 => "CCG ",
          24 => "CAT ",
          25 => "CAC ",
          26 => "CAA ",
          27 => "CAG ",
          28 => "CGT ",
          29 => "CGC ",
          30 => "CGA ",
          31 => "CGG ",
          32 => "ATT ",
          33 => "ATC ",
          34 => "ATA ",
          35 => "ATG ",
          36 => "ACT ",
          37 => "ACC ",
          38 => "ACA ",
          39 => "ACG ",
          40 => "AAT ",
          41 => "AAC ",
          42 => "AAA ",
          43 => "AAG ",
          44 => "AGT ",
          45 => "AGC ",
          46 => "AGA ",
          47 => "AGG ",
          48 => "GTT ",
          49 => "GTC ",
          50 => "GTA ",
          51 => "GTG ",
          52 => "GCT ",
          53 => "GCC ",
          54 => "GCA ",
          55 => "GCG ",
          56 => "GAT ",
          57 => "GAC ",
          58 => "GAA ",
          59 => "GAG ",
          60 => "GGT ",
          61 => "GCG ",
          62 => "GGA ",
          63 => "GGG "
        ];

        $apiTriplets = new TripletApi($this->clientMock, $this->serializerMock);
        static::assertEquals($aTripletsExpected, $apiTriplets::GetTripletsArray($apiTriplets->getTriplets()));
    }
}