<?php
/**
 * Bioapi requests
 * Created 3 november 2019
 * Last modified 18 november 2019
 */
namespace AppBundle\Api;

use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

/**
 * This class makes requests on any API which implements this interface
 * Interface API
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface ApiAdapterInterface
{
    /**
     * ApiInterface constructor.
     * @param   Client        $bioapiClient
     * @param   Serializer    $serializer
     * @param   string        $apiKey
     */
    public function __construct(Client $bioapiClient, Serializer $serializer, $apiKey = null);

    /**
     * @return array
     */
    public function getElements();

    /**
     * @return array|\JMS\Serializer\scalar|mixed|object
     */
    public function getWater();

    /**
     * @param $id
     * @return array|\JMS\Serializer\scalar|mixed|object
     */
    public function getPkValueById($id);

    /**
     * @return array
     */
    public function getTripletsGroups();

    /**
     * @return array
     */
    public function getTriplets();

    /**
     * @return array
     */
    public function getTripletsCombinations();

    /**
     * @return array
     */
    public function getSpeciesNames();

    /**
     * @return array
     */
    public function getTripletsList();

    /**
     * @return array
     */
    public function getTypeIIEndonucleases();

    /**
     * @return array
     */
    public function getTypeIIEndonucleasesForRest();

    /**
     * @return array
     */
    public function getTypeIIsEndonucleases();

    /**
     * @return array
     */
    public function getTypeIIbEndonucleases();

    /**
     * @return array
     */
    public function getReductions();

    /**
     * @param $type
     * @return array
     */
    public function getAlphabetInfos($type);

    /**
     * @return array
     */
    public function getVendors();

    /**
     * @return array
     */
    public function getVendorLinks();

    /**
     * @return array
     */
    public function getPam250Matrix();
}