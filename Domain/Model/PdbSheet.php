<?php
/**
 * One SHEET secondary-structure strand record from a PDB file
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 13 August 2026
 */
namespace Amelaye\BioPHP\Domain\Model;

/**
 * Class PdbSheet
 * @package Amelaye\BioPHP\Domain\Model
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PdbSheet
{
    /**
     * @var string
     */
    private $sheetId = "";

    /**
     * @var int
     */
    private $strand = 0;

    /**
     * @var string
     */
    private $initResName = "";

    /**
     * @var string
     */
    private $initChainId = "";

    /**
     * @var int
     */
    private $initSeqNum = 0;

    /**
     * @var string
     */
    private $endResName = "";

    /**
     * @var string
     */
    private $endChainId = "";

    /**
     * @var int
     */
    private $endSeqNum = 0;

    /**
     * @return string
     */
    public function getSheetId(): string
    {
        return $this->sheetId;
    }

    /**
     * @param string $sheetId
     */
    public function setSheetId(string $sheetId): void
    {
        $this->sheetId = $sheetId;
    }

    /**
     * @return int
     */
    public function getStrand(): int
    {
        return $this->strand;
    }

    /**
     * @param int $strand
     */
    public function setStrand(int $strand): void
    {
        $this->strand = $strand;
    }

    /**
     * @return string
     */
    public function getInitResName(): string
    {
        return $this->initResName;
    }

    /**
     * @param string $initResName
     */
    public function setInitResName(string $initResName): void
    {
        $this->initResName = $initResName;
    }

    /**
     * @return string
     */
    public function getInitChainId(): string
    {
        return $this->initChainId;
    }

    /**
     * @param string $initChainId
     */
    public function setInitChainId(string $initChainId): void
    {
        $this->initChainId = $initChainId;
    }

    /**
     * @return int
     */
    public function getInitSeqNum(): int
    {
        return $this->initSeqNum;
    }

    /**
     * @param int $initSeqNum
     */
    public function setInitSeqNum(int $initSeqNum): void
    {
        $this->initSeqNum = $initSeqNum;
    }

    /**
     * @return string
     */
    public function getEndResName(): string
    {
        return $this->endResName;
    }

    /**
     * @param string $endResName
     */
    public function setEndResName(string $endResName): void
    {
        $this->endResName = $endResName;
    }

    /**
     * @return string
     */
    public function getEndChainId(): string
    {
        return $this->endChainId;
    }

    /**
     * @param string $endChainId
     */
    public function setEndChainId(string $endChainId): void
    {
        $this->endChainId = $endChainId;
    }

    /**
     * @return int
     */
    public function getEndSeqNum(): int
    {
        return $this->endSeqNum;
    }

    /**
     * @param int $endSeqNum
     */
    public function setEndSeqNum(int $endSeqNum): void
    {
        $this->endSeqNum = $endSeqNum;
    }
}
