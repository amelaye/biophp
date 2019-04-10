<?php
/**
 * Class RestrictionEnzymeDigestType
 * Freely inspired by BioPHP's project biophp.org
 * Created 7 april 2019
 * Last modified 7 april 2019
 */
namespace MinitoolsBundle\Entity;

/**
 * Entity RestrictionEnzymeDigest for RestrictionEnzymeDigestType
 * @package MinitoolsBundle\Form
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class RestrictionEnzymeDigest
{
    /**
     * @var int
     */
    private $sequence;

    /**
     * @var bool
     */
    private $showCode;

    /**
     * @var int
     */
    private $minimum;

    /**
     * @var int
     */
    private $retype;

    /**
     * @var string
     */
    private $wre;

    /**
     * @var bool
     */
    private $defined;

    /**
     * @var bool
     */
    private $IIb;

    /**
     * @var bool
     */
    private $IIs;

    /**
     * @var bool
     */
    private $onlydiff;

    /**
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param int $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * @return bool
     */
    public function isShowCode()
    {
        return $this->showCode;
    }

    /**
     * @param bool $showCode
     */
    public function setShowCode($showCode)
    {
        $this->showCode = $showCode;
    }

    /**
     * @return int
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * @param int $minimum
     */
    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;
    }

    /**
     * @return int
     */
    public function getRetype()
    {
        return $this->retype;
    }

    /**
     * @param int $retype
     */
    public function setRetype($retype)
    {
        $this->retype = $retype;
    }

    /**
     * @return string
     */
    public function getWre()
    {
        return $this->wre;
    }

    /**
     * @param string $wre
     */
    public function setWre($wre)
    {
        $this->wre = $wre;
    }

    /**
     * @return bool
     */
    public function isDefined()
    {
        return $this->defined;
    }

    /**
     * @param bool $defined
     */
    public function setDefined($defined)
    {
        $this->defined = $defined;
    }

    /**
     * @return bool
     */
    public function isIIb()
    {
        return $this->IIb;
    }

    /**
     * @param bool $IIb
     */
    public function setIIb($IIb)
    {
        $this->IIb = $IIb;
    }

    /**
     * @return bool
     */
    public function isIIs()
    {
        return $this->IIs;
    }

    /**
     * @param bool $IIs
     */
    public function setIIs($IIs)
    {
        $this->IIs = $IIs;
    }

    /**
     * @return bool
     */
    public function isOnlydiff()
    {
        return $this->onlydiff;
    }

    /**
     * @param bool $onlydiff
     */
    public function setOnlydiff($onlydiff)
    {
        $this->onlydiff = $onlydiff;
    }
}