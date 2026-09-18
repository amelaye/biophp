<?php
/**
 * Contract for one SHEET secondary-structure strand record from a PDB file
 * Freely inspired by BioPHP's project biophp.org
 * Created 18 September 2026
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Interfaces;

/**
 * Interface PdbSheetInterface
 * @package Amelaye\BioPHP\Domain\Parser\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface PdbSheetInterface
{
    /**
     * @return string
     */
    public function getSheetId(): string;

    /**
     * @param string $sheetId
     */
    public function setSheetId(string $sheetId): void;

    /**
     * @return int
     */
    public function getStrand(): int;

    /**
     * @param int $strand
     */
    public function setStrand(int $strand): void;

    /**
     * @return string
     */
    public function getInitResName(): string;

    /**
     * @param string $initResName
     */
    public function setInitResName(string $initResName): void;

    /**
     * @return string
     */
    public function getInitChainId(): string;

    /**
     * @param string $initChainId
     */
    public function setInitChainId(string $initChainId): void;

    /**
     * @return int
     */
    public function getInitSeqNum(): int;

    /**
     * @param int $initSeqNum
     */
    public function setInitSeqNum(int $initSeqNum): void;

    /**
     * @return string
     */
    public function getEndResName(): string;

    /**
     * @param string $endResName
     */
    public function setEndResName(string $endResName): void;

    /**
     * @return string
     */
    public function getEndChainId(): string;

    /**
     * @param string $endChainId
     */
    public function setEndChainId(string $endChainId): void;

    /**
     * @return int
     */
    public function getEndSeqNum(): int;

    /**
     * @param int $endSeqNum
     */
    public function setEndSeqNum(int $endSeqNum): void;
}
