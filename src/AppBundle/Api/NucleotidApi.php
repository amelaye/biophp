<?php
/**
 * Database of elements - Nucleotids
 * Inspired by BioPHP's project biophp.org
 * Created 19 December 2019
 * Last modified 19 December 2019
 */
namespace AppBundle\Api;

use AppBundle\Api\DTO\NucleotidDTO;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

/**
 * Database of elements - Nucleotids
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class NucleotidApi
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
     * List of RNA nucleotids
     * @return array
     */
    public function getNucleotids()
    {
        $uri = '/nucleotids';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aNucleotids = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $nucleotid = new Nucleotid();
            $nucleotid->setLetter($data["letter"]);
            $nucleotid->setComplement($data["complement"]);
            $nucleotid->setNature($data["nature"]);
            $nucleotid->setWeigth($data["weight"]);
            $aNucleotids[] = $nucleotid;
        }
        return $aNucleotids;
    }

    /**
     * List of DNA nucleotids
     * @return array
     */
    public static function GetNucleotidsDNA($aNucleotids)
    {
        $newData = array();

        foreach($aNucleotids as $key => $elem) {
            if($elem->setNature == "DNA") {
                $newData[] = $elem;
            }
        }

        return $newData;
    }

    /**
     * List of RNA nucleotids
     * @return array
     */
    public static function GetNucleotidsRNA($aNucleotids)
    {
        $newData = array();

        foreach($aNucleotids as $key => $elem) {
            if($elem->setNature == "RNA") {
                $newData[] = $elem;
            }
        }

        return $newData;
    }

    /**
     * List of DNA nucleotids complements
     * @return array
     */
    public static function GetDNAComplement($aNucleotids)
    {
        $nucleos = self::GetNucleotidsDNA($aNucleotids);
        $dnaComplements = array();
        foreach($nucleos as $nucleo) {
            $dnaComplements[$nucleo->getLetter()] = $nucleo->getComplement();
        }
        return $dnaComplements;
    }

    /**
     * @return array
     */
    public static function GetDNAWeight($aNucleotids)
    {
        $nucleos = self::GetNucleotidsRNA($aNucleotids);
        $dnaWeights = array();
        foreach($nucleos as $nucleo) {
            $dnaWeights[$nucleo->getLetter()] = $nucleo->getWeight();
        }
        return $dnaWeights;
    }
}