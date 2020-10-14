<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\NucleotidApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp;

class NucleotidApiTest extends WebTestCase
{
    public function setUp()
    {
        $aNucleoObjects = [];

        require 'samples/Nucleotids.php';

        $this->aNucleoObjects = $aNucleoObjects;
        $this->aDNAExpected = $aDNA;
        $this->aRNAExpected = $aRNA;
        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetNucleotids()
    {
        $apiNucleo = new NucleotidApi($this->clientMock, $this->serializerMock);
        static::assertEquals($this->aNucleoObjects, $apiNucleo->getNucleotids());
    }

    public function testGetNucleotidsDNA()
    {
        $apiNucleo = new NucleotidApi($this->clientMock, $this->serializerMock);

        $dna = $apiNucleo::GetNucleotidsDNA($apiNucleo->getNucleotids());
        static::assertEquals($this->aDNAExpected, $dna);
    }

    public function testGetNucleotidsRNA()
    {
        $apiNucleo = new NucleotidApi($this->clientMock, $this->serializerMock);

        $rna = $apiNucleo::GetNucleotidsRNA($apiNucleo->getNucleotids());
        static::assertEquals($this->aRNAExpected, $rna);
    }

    public function testGetDNAComplement()
    {
        $dnaComplementExpected = [
          "A" => "T",
          "T" => "A",
          "G" => "C",
          "C" => "G"
        ];
        $apiNucleo = new NucleotidApi($this->clientMock, $this->serializerMock);
        $dnaComplement = $apiNucleo::GetDNAComplement($apiNucleo->getNucleotids());
        static::assertEquals($dnaComplementExpected, $dnaComplement);
    }

    public function testGetRNAComplement()
    {
        $rnaComplementExpected = [
            "A" => "U",
            "U" => "A",
            "G" => "C",
            "C" => "G"
        ];
        $apiNucleo = new NucleotidApi($this->clientMock, $this->serializerMock);
        $rnaComplement = $apiNucleo::GetRNAComplement($apiNucleo->getNucleotids());
        static::assertEquals($rnaComplementExpected, $rnaComplement);
    }

    public function testGetDNAWeight()
    {
        $weightsExpected = [
          "A" => 313.245,
          "T" => 304.225,
          "G" => 329.245,
          "C" => 289.215
        ];
        $apiNucleo = new NucleotidApi($this->clientMock, $this->serializerMock);
        $weights = $apiNucleo::GetDNAWeight($apiNucleo->getNucleotids());
        static::assertEquals($weightsExpected, $weights);
    }

    public function testGetRNAWeight()
    {
        $weightsExpected = [
            "A" => 329.245,
            "U" => 306.195,
            "G" => 345.245,
            "C" => 305.215
        ];
        $apiNucleo = new NucleotidApi($this->clientMock, $this->serializerMock);
        $weights = $apiNucleo::GetRNAWeight($apiNucleo->getNucleotids());
        static::assertEquals($weightsExpected, $weights);
    }
}