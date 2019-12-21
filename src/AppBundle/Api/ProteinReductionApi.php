<?php
/**
 * Database of elements - Protein reductions
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace AppBundle\Api;

use AppBundle\Api\DTO\ProteinReductionDTO;
use AppBundle\Api\Interfaces\ProteinReductionApiAdapter;

/**
 * Class ProteinReductionApi
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ProteinReductionApi extends Bioapi implements ProteinReductionApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getReductions() : array
    {
        $uri = '/protein_reductions';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aReductions = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $reduction = new ProteinReductionDTO();
            $reduction->setAlphabet($elem["alphabet"]);
            $reduction->setLetters($elem["letters"]);
            $reduction->setPattern($elem["pattern"]);
            $reduction->setNature($elem["nature"]);
            $reduction->setReduction($elem["reduction"]);
            $reduction->setDescription($elem["description"]);
            $aReductions[] = $reduction;
        }

        return $aReductions;
    }

    /**
     * Gets a list as array
     * @param   array   $aReductions
     * @return  array
     */
    public static function GetReductionsArray(array $aReductions) : array
    {
        $newData = array();
        foreach($aReductions as $key => $elem) {
            $newData[$elem->getAlphabet()]["pattern"][] = '/'.$elem->getPattern().'/';
            $newData[$elem->getAlphabet()]["reduction"][] = $elem->getReduction();
        }
        return $newData;
    }

    /**
     * @param   array   $aReductions
     * @param   string  $type
     * @return  array
     */
    public static function GetAlphabetInfos(array $aReductions, string $type) : array
    {
        $newData = array();
        foreach($aReductions as $key => $elem) {
            if($elem["alphabet"] == $type) {
                $newData["Description"] = $elem->getDescription();
                $newData["Elements"][str_replace("|","",$elem->getPattern())] = $elem->getNature();
            }
        }
        return($newData);
    }
}