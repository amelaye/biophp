<?php
/**
 * Database of elements - Nucleotids
 * Inspired by BioPHP's project biophp.org
 * Created 19 December 2019
 * Last modified 21 December 2019
 */
namespace AppBundle\Api;

use AppBundle\Api\DTO\NucleotidDTO;
use AppBundle\Api\Interfaces\NucleotidApiAdapter;

/**
 * Database of elements - Nucleotids
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class NucleotidApi extends Bioapi implements NucleotidApiAdapter
{
    /**
     * List of DNA/RNA nucleotids
     * @return      array
     */
    public function getNucleotids() : array
    {
        $uri = '/nucleotids';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aNucleotids = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $nucleotid = new NucleotidDTO();
            $nucleotid->setLetter($elem["letter"]);
            $nucleotid->setComplement($elem["complement"]);
            $nucleotid->setNature($elem["nature"]);
            $nucleotid->setWeight($elem["weight"]);
            $aNucleotids[] = $nucleotid;
        }
        return $aNucleotids;
    }

    /**
     * List of DNA nucleotids
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetNucleotidsDNA(array $aNucleotids) : array
    {
        $newData = array();

        foreach($aNucleotids as $key => $elem) {
            if($elem->getNature() == "DNA") {
                $newData[] = $elem;
            }
        }

        return $newData;
    }

    /**
     * List of RNA nucleotids
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetNucleotidsRNA(array $aNucleotids) : array
    {
        $newData = array();

        foreach($aNucleotids as $key => $elem) {
            if($elem->getNature() == "RNA") {
                $newData[] = $elem;
            }
        }

        return $newData;
    }

    /**
     * List of DNA nucleotids complements
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetDNAComplement(array $aNucleotids) : array
    {
        $nucleos = self::GetNucleotidsDNA($aNucleotids);
        $dnaComplements = array();
        foreach($nucleos as $nucleo) {
            $dnaComplements[$nucleo->getLetter()] = $nucleo->getComplement();
        }
        return $dnaComplements;
    }

    /**
     * List of RNA nucleotids complements
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetRNAComplement(array $aNucleotids) : array
    {
        $nucleos = self::GetNucleotidsRNA($aNucleotids);
        $dnaComplements = array();
        foreach($nucleos as $nucleo) {
            $dnaComplements[$nucleo->getLetter()] = $nucleo->getComplement();
        }
        return $dnaComplements;
    }

    /**
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetDNAWeight(array $aNucleotids) : array
    {
        $nucleos = self::GetNucleotidsDNA($aNucleotids);
        $dnaWeights = array();
        foreach($nucleos as $nucleo) {
            $dnaWeights[$nucleo->getLetter()] = $nucleo->getWeight();
        }
        return $dnaWeights;
    }

    /**
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetRNAWeight(array $aNucleotids) : array
    {
        $nucleos = self::GetNucleotidsRNA($aNucleotids);
        $dnaWeights = array();
        foreach($nucleos as $nucleo) {
            $dnaWeights[$nucleo->getLetter()] = $nucleo->getWeight();
        }
        return $dnaWeights;
    }
}