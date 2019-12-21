<?php


namespace AppBundle\Api;


class VendorLinkApi
{
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
}