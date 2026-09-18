<?php
/**
 * Contract for one HELIX secondary-structure record from a PDB file
 * Freely inspired by BioPHP's project biophp.org
 * Created 18 September 2026
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Interfaces;

/**
 * Interface PdbHelixInterface
 * @package Amelaye\BioPHP\Domain\Parser\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface PdbHelixInterface
{
    /**
     * @return string
     */
    public function getHelixId(): string;

    /**
     * @param string $helixId
     */
    public function setHelixId(string $helixId): void;

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

    /**
     * @return int
     */
    public function getHelixClass(): int;

    /**
     * @param int $helixClass
     */
    public function setHelixClass(int $helixClass): void;

    /**
     * @return int
     */
    public function getLength(): int;

    /**
     * @param int $length
     */
    public function setLength(int $length): void;
}
