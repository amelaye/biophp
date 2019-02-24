<?php
/**
 * Entity used by form DnaToProteinType
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 february 2019
 * Last modified 24 february 2019
 */
namespace MinitoolsBundle\Entity;


class DnaToProtein
{
    /**
     * @var string
     */
    private $sequence;

    private $frames;

    private $dgaps;

    private $showAligned;

    private $searchOrfs;

    private $protsize;

    private $onlyCoding;

    private $trimmed;

    private $genetic_code;

    private $usemycode;

    private $mycode;


    public function getSequence()
    {
        return $this->sequence;
    }
    public function setSequence($sequence)
    {
        $this->sequence = strtoupper($sequence);
    }


    public function getFrames()
    {
        return $this->frames;
    }
    public function setFrames($frames)
    {
        $this->frames = $frames;
    }


    public function getDgaps()
    {
        return $this->dgaps;
    }
    public function setDgaps($dgaps)
    {
        $this->dgaps = $dgaps;
    }


    public function getShowAligned()
    {
        return $this->showAligned;
    }
    public function setShowAligned($showAligned)
    {
        $this->showAligned = $showAligned;
    }


    public function getSearchOrfs()
    {
        return $this->searchOrfs;
    }
    public function setSearchOrfs($searchOrfs)
    {
         $this->searchOrfs = $searchOrfs;
    }


    public function getProtsize()
    {
        return $this->protsize;
    }
    public function setProtsize($protsize)
    {
        $this->protsize = $protsize;
    }


    public function getOnlyCoding()
    {
        return $this->onlyCoding;
    }
    public function setOnlyCoding($onlyCoding)
    {
        $this->onlyCoding = $onlyCoding;
    }


    public function getTrimmed()
    {
        return $this->trimmed;
    }
    public function setTrimmed($trimmed)
    {
        $this->trimmed = $trimmed;
    }


    public function getGeneticCode()
    {
        return $this->genetic_code;
    }
    public function setGeneticCode($genetic_code)
    {
        $this->genetic_code = $genetic_code;
    }


    public function getUsemycode()
    {
        return $this->usemycode;
    }
    public function setUsemycode($usemycode)
    {
        $this->usemycode = $usemycode;
    }


    public function getMycode()
    {
        return $this->mycode;
    }
    public function setMycode($mycode)
    {
        $this->mycode = strtoupper($mycode);
    }
}