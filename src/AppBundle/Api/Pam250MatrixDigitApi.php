<?php


namespace AppBundle\Api;


class Pam250MatrixDigitApi extends Bioapi
{
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