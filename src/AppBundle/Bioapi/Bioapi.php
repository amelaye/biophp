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

    public function getNucleotidsRNA()
    {
        $uri = '/nucleotids';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();

        foreach($data["hydra:member"] as $key => $elem) {
            if($elem["nature"] == "RNA") {
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

    public function getDNAWeight()
    {
        $nucleos = $this->getNucleotidsDNA();
        $dnaWeights = array();
        foreach($nucleos as $nucleo) {
            $dnaWeights[$nucleo["letter"]] = $nucleo["weigth"];
        }
        return $dnaWeights;
    }

    public function getRNAWeight()
    {
        $nucleos = $this->getNucleotidsRNA();
        $dnaWeights = array();
        foreach($nucleos as $nucleo) {
            $dnaWeights[$nucleo["letter"]] = $nucleo["weigth"];
        }
        return $dnaWeights;
    }

    /**
     * TM Base Stacking
     * Basic temperatures of nucleotids combinations
     */
    public function getEnthropyValues()
    {
        $uri = '/tm_base_stackings';
        $response = $this->bioapiClient->get($uri);
        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem['id']] = $elem['temperatureEnthropy'];
        }
        return $newData;
    }

    public function getEnthalpyValues()
    {
        $uri = '/tm_base_stackings';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem['id']] = $elem['temperatureEnthalpy'];
        }
        return $newData;
    }

    public function getWater()
    {
        $uri = '/elements/6';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        return $data;
    }

    /**
     * @param $id
     * @return array|\JMS\Serializer\scalar|mixed|object
     */
    public function getPkValueById($id)
    {
        $uri = '/p_ks/'.$id;
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        return array_change_key_case($data, CASE_UPPER);
    }
}