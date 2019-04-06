<?php
/**
 * Entity used by form DnaToProteinType
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 february 2019
 * Last modified 3 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DnaToProtein
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class DnaToProtein
{
    private $sSequence;

    private $aFrames;

    private $bDgaps;

    private $bShowAligned;

    private $bSearchOrfs;

    /**
     * @Assert\GreaterThanOrEqual(
     *     value = 10
     * )
     */
    private $iProtsize;

    private $bOnlyCoding;

    private $bTrimmed;

    private $sGeneticCode;

    private $bUsemycode;

    /**
     * @Assert\Length(
     *      min = 64,
     *      max = 64,
     *      minMessage = "The custom code is not correct (is not 64 characters long).",
     *      maxMessage = "The custom code is not correct (is not 64 characters long)."
     * )
     */
    private $sMycode;


    public function getSequence()
    {
        return $this->sSequence;
    }
    public function setSequence($sSequence)
    {
        $this->sSequence = strtoupper($sSequence);
    }


    public function getFrames()
    {
        return $this->aFrames;
    }
    public function setFrames($aFrames)
    {
        $this->aFrames = $aFrames;
    }


    public function getDgaps()
    {
        return $this->bDgaps;
    }
    public function setDgaps($bDgaps)
    {
        $this->bDgaps = $bDgaps;
    }


    public function getShowAligned()
    {
        return $this->bShowAligned;
    }
    public function setShowAligned($bShowAligned)
    {
        $this->bShowAligned = $bShowAligned;
    }


    public function getSearchOrfs()
    {
        return $this->bSearchOrfs;
    }
    public function setSearchOrfs($bSearchOrfs)
    {
         $this->bSearchOrfs = $bSearchOrfs;
    }


    public function getProtsize()
    {
        return $this->iProtsize;
    }
    public function setProtsize($iProtsize)
    {
        $this->iProtsize = $iProtsize;
    }


    public function getOnlyCoding()
    {
        return $this->bOnlyCoding;
    }
    public function setOnlyCoding($bOnlyCoding)
    {
        $this->bOnlyCoding = $bOnlyCoding;
    }


    public function getTrimmed()
    {
        return $this->bTrimmed;
    }
    public function setTrimmed($bTrimmed)
    {
        $this->bTrimmed = $bTrimmed;
    }


    public function getGeneticCode()
    {
        return $this->sGeneticCode;
    }
    public function setGeneticCode($sGeneticCode)
    {
        $this->sGeneticCode = $sGeneticCode;
    }


    public function getUsemycode()
    {
        return $this->bUsemycode;
    }
    public function setUsemycode($bUsemycode)
    {
        $this->bUsemycode = $bUsemycode;
    }


    public function getMycode()
    {
        return $this->sMycode;
    }
    public function setMycode($sMycode)
    {
        $this->sMycode = strtoupper($sMycode);
    }
}