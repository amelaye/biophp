<?php
/**
 * Bioapi requests
 * Created 3 november 2019
 * Last modified 3 november 2019
 */
namespace AppBundle\Bioapi;

use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

/**
 * This class makes requests on the Bio API api.amelayes-biophp.net
 * This is the sample database
 * Class Bioapi
 * @package AppBundle\Bioapi
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface BioapiInterface
{
    /**
     * List of DNA nucleotids
     * @return array
     */
    public function getNucleotidsDNA();

    /**
     * List of RNA nucleotids
     * @return array
     */
    public function getNucleotidsRNA();

    /**
     * List of DNA nucleotids complements
     * @return array
     */
    public function getDNAComplement();

    /**
     * List of RNA nucleotids complements
     * @return array
     */
    public function getRNAComplement();

    /**
     * @return array
     */
    public function getDNAWeight();

    /**
     * @return array
     */
    public function getRNAWeight();

    /**
     * TM Base Stacking
     * Basic temperatures of nucleotids combinations
     * @return array
     */
    public function getEnthropyValues();

    /**
     * @return array
     */
    public function getEnthalpyValues();

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
    public function getAminos();

    /**
     * @return array
     */
    public function getAminoweights();

    /**
     * @return array
     */
    public function getAminosOnlyLetters();

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