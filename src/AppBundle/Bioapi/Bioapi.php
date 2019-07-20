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

    public function getAminos()
    {
        $uri = '/aminos';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        //return $data["hydra:member"];
        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem['id']] = $elem;
        }

        return $newData;
    }

    public function getAminosOnlyLetters()
    {
        $uri = '/aminos';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem['name']] = [1 => $elem["name1Letter"], 3 => $elem["name3Letters"]];
        }

        return $newData;
    }

    public function getTripletsGroups()
    {
        $uri = '/triplet_species';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[str_replace(" ", "_", $elem['nature'])] = $elem['tripletsGroups'];
        }

        return $newData;

    }

    public function getTriplets()
    {
        $uri = '/triplet_species';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[str_replace(" ", "_", $elem['nature'])] = $elem['triplets'];
        }

        return $newData;
    }

    public function getTripletsCombinations()
    {
        $uri = '/triplet_species';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData = $newData + $elem["triplets"];
        }
        return $newData;
    }

    public function getSpeciesNames()
    {
        $uri = '/triplet_species';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[ucwords($elem['nature'])] = str_replace(" ", "_", $elem['nature']);
        }

        return $newData;
    }

    public function getTripletsList()
    {
        $uri = '/triplets';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[] = $elem['triplet']." ";
        }

        return $newData;
    }

    public function getTypeIIEndonucleases()
    {
        $uri = '/type_i_i_endonucleases';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem["id"]] = [
                $elem["samePattern"][0],
                $elem["recognitionPattern"],
                $elem["computingPattern"],
                $elem["lengthRecognitionPattern"],
                $elem["cleavagePosUpper"],
                $elem["cleavagePosLower"],
                $elem["nbNonNBases"],
            ];
        }

        return $newData;
    }

    public function getTypeIIsEndonucleases()
    {
        $uri = '/type_i_is_endonucleases';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem["id"]] = [
                $elem["samePattern"][0],
                $elem["recognitionPattern"],
                $elem["computingPattern"],
                $elem["lengthRecognitionPattern"],
                $elem["cleavagePosUpper"],
                $elem["cleavagePosLower"],
                $elem["nbNonNBases"],
            ];
        }

        return $newData;
    }

    public function getTypeIIbEndonucleases()
    {
        $uri = '/type_i_ib_endonucleases';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem["id"]] = [
                $elem["samePattern"][0],
                $elem["recognitionPattern"],
                $elem["computingPattern"],
                $elem["lengthRecognitionPattern"],
                $elem["cleavagePosUpper"],
                $elem["cleavagePosLower"],
                $elem["nbNonNBases"],
            ];
        }

        return $newData;
    }

    public function getReductions()
    {
        $uri = '/protein_reductions';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem["alphabet"]]["pattern"][] = '/'.$elem["pattern"].'/';
            $newData[$elem["alphabet"]]["reduction"][] = $elem["reduction"];
        }

        return $newData;
    }

    public function getAlphabetInfos($type)
    {
        $uri = '/protein_reductions';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            if($elem["alphabet"] == $type) {
                $newData["Description"] = $elem["description"];
                $newData["Elements"][str_replace("|","",$elem["pattern"])] = $elem["nature"];
            }
        }
        return($newData);
    }
}