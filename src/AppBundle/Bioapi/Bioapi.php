<?php

namespace AppBundle\Bioapi;

use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

class Bioapi
{
    private $bioapiClient;
    private $serializer;
    private $apiKey;

    public function __construct(Client $bioapiClient, Serializer $serializer, $apiKey = null)
    {
        $this->bioapiClient = $bioapiClient;
        $this->serializer = $serializer;
        $this->apiKey = $apiKey;
    }

    public function getNucleotidsDNA()
    {
        $uri = '/nucleotids';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();

        foreach($data["hydra:member"] as $key => $elem) {
            if($elem["nature"] == "DNA") {
                $newData[] = $elem;
            }
        }

        return $newData;
    }

    public function getDNAComplement()
    {
        $nucleos = $this->getNucleotidsDNA();
        $dnaComplements = array();
        foreach($nucleos as $nucleo) {
            $dnaComplements[$nucleo["letter"]] = $nucleo["complement"];
        }
        return $dnaComplements;
    }
}