<?php

/**
 * Reader for Fasta Entity
 * Freely inspired by BioPHP's project biophp.org
 * Created 1st september 2019
 * Last modified 1st september 2019
 */
namespace AppBundle\Entity;

use Deployer\Component\PharUpdate\Exception\FileException;

/**
 * Class Reader - Reader for Fasta Entity
 * @package AppBundle\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class Reader
{
    /**
     * Content of file INPUT
     * @var resource
     */
    public $oObject;

    /**
     * List of ID unique
     * @var int
     */
    public $iUid;

    /**
     * File input
     * @var string
     */
    private $sInputFile;

    /**
     * Type file
     * @var string
     */
    private $sTypeFile;

    /**
     * Data of id current
     * @var int
     */
    private $iCurrent;

    /**
     * File number lines
     * @var int
     */
    private $iNumberLines;

    /**
     * Number of unique elements
     * @var int
     */
    private $iNumberUid;

    /**
     * @var bool
     */
    private $bReadFile = false;

    /**
     * @var string
     */
    private $sType;


    /**
     * @return int
     */
    public function getNumberLines()
    {
        return $this->iNumberLines;
    }

    /**
     * @param $numberLines
     */
    public function setNumberLines($numberLines)
    {
        $this->iNumberLines = $numberLines;
    }

    /**
     * @return string
     */
    public function getInputFile()
    {
        return $this->sInputFile;
    }

    /**
     * @param $sInputFile
     */
    public function setInputFile($sInputFile)
    {
        $this->sInputFile = $sInputFile;
    }

    /**
     * @return string
     */
    public function getTypeFile()
    {
        return $this->sTypeFile;
    }

    /**
     * @param $sTypeFile
     */
    public function setTypeFile($sTypeFile)
    {
        $this->sTypeFile = $sTypeFile;
    }

    /**
     * @return int
     */
    public function getUid() {
        return $this->iUid;
    }

    /**
     * @param $iUid
     */
    public function setUid($iUid) {
        $this->iUid = $iUid;
    }

    /**
     * @return int
     */
    public function getNumberUid() {
        return $this->iNumberUid;
    }

    /**
     * @param $iNumberUid
     */
    public function setNumberUid($iNumberUid) {
        $this->iNumberUid = $iNumberUid;
    }

    /**
     * @return bool
     */
    public function isReadFile()
    {
        return $this->bReadFile;
    }

    /**
     * @param bool $bReadFile
     */
    public function setReadFile($bReadFile)
    {
        $this->bReadFile = $bReadFile;
    }

    /**
     * @return resource
     */
    public function getObject()
    {
        return $this->oObject;
    }

    /**
     * @param resource $oObject
     */
    public function setObject($oObject)
    {
        $this->oObject = $oObject;
    }
}