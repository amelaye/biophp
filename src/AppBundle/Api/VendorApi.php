<?php


namespace AppBundle\Api;


class VendorApi extends Bioapi
{
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

}