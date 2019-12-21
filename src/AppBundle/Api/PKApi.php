<?php
/**
 * Database of elements - PK
 * Inspired by BioPHP's project biophp.org
 * Created 20 December 2019
 * Last modified 20 December 2019
 */
namespace AppBundle\Api;

/**
 * Database of elements - Nucleotids
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PKApi extends Bioapi
{
    /**
     * @param $id
     * @return array
     */
    public function getPkValueById($id) : array
    {
        $uri = '/p_ks/'.$id;
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        return array_change_key_case($data, CASE_UPPER);
    }
}