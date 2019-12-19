<?php
/**
 * Bioapi requests
 * Created 3 november 2019
 * Last modified 18 november 2019
 */
namespace AppBundle\Api;

use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

/**
 * This class makes requests on the Bio API api.amelayes-biophp.net
 * This is the sample database - implements ApiAdapterInterface
 * Class Bioapi
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class Bioapi implements ApiAdapterInterface
{
    /**
     * @var Client
     */
    private $bioapiClient;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * Bioapi constructor.
     * @param Client        $bioapiClient
     * @param Serializer    $serializer
     * @param string        $apiKey
     */
    public function __construct(Client $bioapiClient, Serializer $serializer, $apiKey = null)
    {
        $this->bioapiClient = $bioapiClient;
        $this->serializer   = $serializer;
        $this->apiKey       = $apiKey;
    }

    /**
     * TM Base Stacking
     * Basic temperatures of nucleotids combinations
     * @return array
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function getElements()
    {
        $uri = '/elements';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');
        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem['name']] = $elem['weight'];
        }
        return $newData;
    }

    /**
     * @return array|\JMS\Serializer\scalar|mixed|object
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function getTypeIIEndonucleasesForRest()
    {
        $uri = '/type_i_i_endonucleases';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $sPattern = $MaVariable = str_replace("'", "", $elem["recognitionPattern"]);
            $sPattern = $MaVariable = str_replace("_", "", $sPattern);
            $newData[$elem["id"]] = [
                $sPattern,
                $elem["cleavagePosUpper"],
            ];
        }

        return $newData;
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @param $type
     * @return array
     */
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

    /**
     * @return array
     */
    public function getVendors()
    {
        $uri = '/vendors';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem["id"]] = $elem["vendor"];
        }
        return($newData);
    }

    /**
     * @return array
     */
    public function getVendorLinks()
    {
        $uri = '/vendor_links';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem["id"]] = ["name" => $elem["name"], "url" => $elem["link"]];
        }
        return($newData);
    }

    /**
     * @return array
     */
    public function getPam250Matrix()
    {
        $uri = '/pam250_matrix_digits';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $newData = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $newData[$elem["id"]] = $elem["value"];
        }
        return($newData);
    }
}