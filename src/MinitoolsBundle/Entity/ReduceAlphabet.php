<?php
/**
 * Class ReduceAlphabet
 * Freely inspired by BioPHP's project biophp.org
 * Created 7 april 2019
 * Last modified 7 april 2019
 */
namespace MinitoolsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity used by form ReduceAlphabetType
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class ReduceAlphabet
{
    /**
     * @var string
     */
    private $seq;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     * @Assert\Length(
     *      min = 20,
     *      max = 20,
     *      minMessage = "The personalized alphabet is not correct",
     *      maxMessage = "The personalized alphabet is not correct"
     * )
     */
    private $customAlphabet;

    /**
     * @var int
     */
    private $aaperline;

    /**
     * @var bool
     */
    private $showReduced;

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
        // change the sequence to upper case
        $seq = strtoupper($seq);
        // remove non-coding characters([^ARNDCEQGHILKMFPSTWYVX\*])
        $seq = preg_replace ("([^ARNDCEQGHILKMFPSTWYVX\*])", "", $seq);
        $this->seq = $seq;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getCustomAlphabet()
    {
        return $this->customAlphabet;
    }

    /**
     * @param string $customAlphabet
     */
    public function setCustomAlphabet($customAlphabet)
    {
        $customAlphabet = strtoupper($customAlphabet);
        $this->customAlphabet = $customAlphabet;
    }

    /**
     * @return int
     */
    public function getAaperline()
    {
        return $this->aaperline;
    }

    /**
     * @param int $aaperline
     */
    public function setAaperline($aaperline)
    {
        $this->aaperline = $aaperline;
    }

    /**
     * @return bool
     */
    public function isShowReduced()
    {
        return $this->showReduced;
    }

    /**
     * @param bool $showReduced
     */
    public function setShowReduced($showReduced)
    {
        $this->showReduced = $showReduced;
    }
}