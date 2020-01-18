<?php
/**
 * Database of elements - Pam250 Matrix
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace App\Api;

use App\Api\DTO\Pam250MatrixDigitDTO;
use App\Api\Interfaces\Pam250MatrixDigitApiAdapter;

/**
 * Class Pam250MatrixDigitApi
 * @package App\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class Pam250MatrixDigitApi extends Bioapi implements Pam250MatrixDigitApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getPam250Matrix() : array
    {
        $uri = '/pam250_matrix_digits';
        $response = $this->bioapiClient->get($uri);
        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aPam250 = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $oPam250MatrixDigit = new Pam250MatrixDigitDTO();
            $oPam250MatrixDigit->setId($elem["id"]);
            $oPam250MatrixDigit->setValue($elem["value"]);
            $aPam250[] = $oPam250MatrixDigit;
        }
        return($aPam250);
    }

    /**
     * Gets a list as array
     * @param   array   $aPam250
     * @return  array
     */
    public static function GetPam250MatrixArray(array $aPam250) : array
    {
        $newData = array();
        foreach($aPam250 as $key => $elem) {
            $newData[$elem->getId()] = $elem->getValue();
        }
        return($newData);
    }
}