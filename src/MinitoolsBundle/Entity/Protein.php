<?php
/**
 * Entity used by form ProteinPropertiesType
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 31 march 2019
 */
namespace MinitoolsBundle\Entity;

/**
 * Class Protein
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class Protein
{
    /**
     * @var string
     */
    private $seq;

    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $end;

    /**
     * @var bool
     */
    private $composition;

    /**
     * @var bool
     */
    private $molweight;

    /**
     * @var bool
     */
    private $abscoef;

    /**
     * @var bool
     */
    private $charge;

    /**
     * @var int
     */
    private $dataSource;

    /**
     * @var bool
     */
    private $charge2;

    /**
     * @var int
     */
    private $pH;

    /**
     * @var bool
     */
    private $threeLetters;

    /**
     * @var bool
     */
    private $type1;

    /**
     * @var bool
     */
    private $type2;

    /**
     * @return string
     */
    public function getSeq()
    {
        return $this->seq;
    }

    /**
     * @param string $seq
     */
    public function setSeq($seq)
    {
        $this->seq = $seq;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param int $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return int
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param int $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return bool
     */
    public function isComposition()
    {
        return $this->composition;
    }

    /**
     * @param bool $composition
     */
    public function setComposition($composition)
    {
        $this->composition = $composition;
    }

    /**
     * @return bool
     */
    public function isMolweight()
    {
        return $this->molweight;
    }

    /**
     * @param bool $molweight
     */
    public function setMolweight($molweight)
    {
        $this->molweight = $molweight;
    }

    /**
     * @return bool
     */
    public function isAbscoef()
    {
        return $this->abscoef;
    }

    /**
     * @param bool $abscoef
     */
    public function setAbscoef($abscoef)
    {
        $this->abscoef = $abscoef;
    }

    /**
     * @return bool
     */
    public function isCharge()
    {
        return $this->charge;
    }

    /**
     * @param bool $charge
     */
    public function setCharge($charge)
    {
        $this->charge = $charge;
    }

    /**
     * @return int
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @param int $dataSource
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @return bool
     */
    public function isCharge2()
    {
        return $this->charge2;
    }

    /**
     * @param bool $charge2
     */
    public function setCharge2($charge2)
    {
        $this->charge2 = $charge2;
    }

    /**
     * @return int
     */
    public function getPH()
    {
        return $this->pH;
    }

    /**
     * @param int $pH
     */
    public function setPH($pH)
    {
        $this->pH = $pH;
    }

    /**
     * @return bool
     */
    public function isThreeLetters()
    {
        return $this->threeLetters;
    }

    /**
     * @param bool $threeLetters
     */
    public function setThreeLetters($threeLetters)
    {
        $this->threeLetters = $threeLetters;
    }

    /**
     * @return bool
     */
    public function isType1()
    {
        return $this->type1;
    }

    /**
     * @param bool $type1
     */
    public function setType1($type1)
    {
        $this->type1 = $type1;
    }

    /**
     * @return bool
     */
    public function isType2()
    {
        return $this->type2;
    }

    /**
     * @param bool $type2
     */
    public function setType2($type2)
    {
        $this->type2 = $type2;
    }
}