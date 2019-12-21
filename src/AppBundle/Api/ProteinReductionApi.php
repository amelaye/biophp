<?php


namespace AppBundle\Api;


class ProteinReductionApi extends Bioapi
{
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
}