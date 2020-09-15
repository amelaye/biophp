<?php
/**
 * Database of elements - Amino acids
 * Inspired by BioPHP's project biophp.org
 * Created 1st December 2019
 * Last modified 15 September 2020
 */
namespace Amelaye\BioPHP\Api;

use Amelaye\BioPHP\Api\DTO\AminoDTO;
use Amelaye\BioPHP\Api\Interfaces\AminoApiAdapter;

/**
 * Database of aminos - Amino acids
 * @package App\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class AminoApi extends Bioapi implements AminoApiAdapter
{
    /**
     * Retrives Aminos informations : gets a full array of objects
     * @return array
     */
    public function getAminos() : array
    {
        $uri = '/aminos';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aAminos = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $oAminoDTO = new AminoDTO();
            $oAminoDTO->setId($elem["id"]);
            $oAminoDTO->setName($elem["name"]);
            $oAminoDTO->setName1Letter($elem["name1Letter"]);
            $oAminoDTO->setName3Letters($elem["name3Letters"]);
            $oAminoDTO->setWeight1($elem["weight1"]);
            $oAminoDTO->setWeight2($elem["weight2"]);
            $oAminoDTO->setResidueMolWeight(floatval($elem["residueMolWeight"]));
            $aAminos[] = $oAminoDTO;
        }

        return $aAminos;
    }

    /**
     * Creates a simple array of aminos
     * @example $aFormattedAminos["name"] = [$aFormattedAminos["name1Letter"], $aFormattedAminos["name3Letters"]]
     * @param   array   $aAminos    Array of objects
     * @return  array
     */
    public static function GetAminosOnlyLetters(array $aAminos) : array
    {
        $aFormattedAminos = array();
        foreach($aAminos as $key => $elem) {
            $aFormattedAminos[$elem->getName()] = [1 => $elem->getName1Letter(), 3 => $elem->getName3Letters()];
        }

        return $aFormattedAminos;
    }

    /**
     * Creates the simpliest array of aminos
     * @example $aFormattedAminos["name1Letter"] = "name3Letters"
     * @param   array $aAminos
     * @return  array
     */
    public static function GetAminosOneToThreeLetters(array $aAminos) : array
    {
        $aFormattedAminos = array();
        foreach($aAminos as $key => $elem) {
            $aFormattedAminos[$elem->getName1Letter()] = $elem->getName3Letters();
        }

        return $aFormattedAminos;
    }

    /**
     * Creates a simple array juste with aminos weights
     * @param   array     $aAminos    Array of objects
     * @return  array
     */
    public static function GetAminoweights(array $aAminos) : array
    {
        $aFormattedAminos = array();
        foreach($aAminos as $key => $elem) {
            $aFormattedAminos[$elem->getId()] = [$elem->getWeight1(), $elem->getWeight2()];
        }

        return $aFormattedAminos;
    }

    /**
     * Creates a simple array juste with aminos residues molweights
     * @param   array     $aAminos    Array of objects
     * @return  array
     */
    public static function GetAminoResidueWeights(array $aAminos) : array
    {
        $aFormattedAminos = array();
        foreach($aAminos as $key => $elem) {
            $aFormattedAminos[$elem->getId()] = $elem->getResidueMolWeight();
        }

        return $aFormattedAminos;
    }
}