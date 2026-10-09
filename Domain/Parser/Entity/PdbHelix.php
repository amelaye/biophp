<?php
/**
 * One HELIX secondary-structure record from a PDB file
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 8 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Entity;

use Amelaye\BioPHP\Domain\Parser\Interfaces\PdbHelixInterface;

/**
 * Class PdbHelix
 * @package Amelaye\BioPHP\Domain\Parser\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PdbHelix implements PdbHelixInterface
{
    /**
     * @var string
     */
    private string $helixId = "";

    /**
     * @var string
     */
    private string $initResName = "";

    /**
     * @var string
     */
    private string $initChainId = "";

    /**
     * @var int
     */
    private int $initSeqNum = 0;

    /**
     * @var string
     */
    private string $endResName = "";

    /**
     * @var string
     */
    private string $endChainId = "";

    /**
     * @var int
     */
    private int $endSeqNum = 0;

    /**
     * @var int
     */
    private int $helixClass = 0;

    /**
     * @var int
     */
    private int $length = 0;

    /**
     * Insertion code of the first residue (column 26), empty for most residues.
     * @var string
     */
    private string $initICode = "";

    /**
     * Insertion code of the last residue (column 38), empty for most residues.
     * @var string
     */
    private string $endICode = "";

    /**
     * @return string
     */
    public function getHelixId(): string
    {
        return $this->helixId;
    }

    /**
     * @param string $helixId
     */
    public function setHelixId(string $helixId): void
    {
        $this->helixId = $helixId;
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

    /**
     * @return int
     */
    public function getHelixClass(): int
    {
        return $this->helixClass;
    }

    /**
     * @param int $helixClass
     */
    public function setHelixClass(int $helixClass): void
    {
        $this->helixClass = $helixClass;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    /**
     * @return string
     */
    public function getInitICode(): string
    {
        return $this->initICode;
    }

    /**
     * @param string $initICode
     */
    public function setInitICode(string $initICode): void
    {
        $this->initICode = $initICode;
    }

    /**
     * @return string
     */
    public function getEndICode(): string
    {
        return $this->endICode;
    }

    /**
     * @param string $endICode
     */
    public function setEndICode(string $endICode): void
    {
        $this->endICode = $endICode;
    }
}
