<?php
/**
 * Contract for one ATOM/HETATM coordinate record from a PDB file
 * Freely inspired by BioPHP's project biophp.org
 * Created 18 September 2026
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Interfaces;

/**
 * Interface PdbAtomInterface
 * @package Amelaye\BioPHP\Domain\Parser\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface PdbAtomInterface
{
    /**
     * @return int
     */
    public function getSerial(): int;

    /**
     * @param int $serial
     */
    public function setSerial(int $serial): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * @return string
     */
    public function getAltLoc(): string;

    /**
     * @param string $altLoc
     */
    public function setAltLoc(string $altLoc): void;

    /**
     * @return string
     */
    public function getResName(): string;

    /**
     * @param string $resName
     */
    public function setResName(string $resName): void;

    /**
     * @return string
     */
    public function getChainId(): string;

    /**
     * @param string $chainId
     */
    public function setChainId(string $chainId): void;

    /**
     * @return int
     */
    public function getResSeq(): int;

    /**
     * @param int $resSeq
     */
    public function setResSeq(int $resSeq): void;

    /**
     * @return float
     */
    public function getX(): float;

    /**
     * @param float $x
     */
    public function setX(float $x): void;

    /**
     * @return float
     */
    public function getY(): float;

    /**
     * @param float $y
     */
    public function setY(float $y): void;

    /**
     * @return float
     */
    public function getZ(): float;

    /**
     * @param float $z
     */
    public function setZ(float $z): void;

    /**
     * @return float
     */
    public function getOccupancy(): float;

    /**
     * @param float $occupancy
     */
    public function setOccupancy(float $occupancy): void;

    /**
     * @return float
     */
    public function getTempFactor(): float;

    /**
     * @param float $tempFactor
     */
    public function setTempFactor(float $tempFactor): void;

    /**
     * @return string
     */
    public function getElement(): string;

    /**
     * @param string $element
     */
    public function setElement(string $element): void;
}
