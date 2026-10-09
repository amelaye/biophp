<?php
/**
 * One ATOM/HETATM coordinate record from a PDB file
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Entity;

use Amelaye\BioPHP\Domain\Parser\Interfaces\PdbAtomInterface;

/**
 * Class PdbAtom
 * @package Amelaye\BioPHP\Domain\Parser\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PdbAtom implements PdbAtomInterface
{
    /**
     * @var int
     */
    private int $serial = 0;

    /**
     * @var string
     */
    private string $name = "";

    /**
     * @var string
     */
    private string $altLoc = "";

    /**
     * @var string
     */
    private string $resName = "";

    /**
     * @var string
     */
    private string $chainId = "";

    /**
     * @var int
     */
    private int $resSeq = 0;

    /**
     * Insertion code (column 27) : with the chain and resSeq, it names the residue ("52A" follows
     * "52" in Kabat-numbered antibodies). Empty for most residues.
     * @var string
     */
    private string $iCode = "";

    /**
     * The MODEL the atom belongs to (an NMR ensemble holds several) ; 1 when the file has none.
     * @var int
     */
    private int $model = 1;

    /**
     * @var float
     */
    private float $x = 0.0;

    /**
     * @var float
     */
    private float $y = 0.0;

    /**
     * @var float
     */
    private float $z = 0.0;

    /**
     * @var float
     */
    private float $occupancy = 0.0;

    /**
     * @var float
     */
    private float $tempFactor = 0.0;

    /**
     * @var string
     */
    private string $element = "";

    /**
     * @return int
     */
    public function getSerial(): int
    {
        return $this->serial;
    }

    /**
     * @param int $serial
     */
    public function setSerial(int $serial): void
    {
        $this->serial = $serial;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getAltLoc(): string
    {
        return $this->altLoc;
    }

    /**
     * @param string $altLoc
     */
    public function setAltLoc(string $altLoc): void
    {
        $this->altLoc = $altLoc;
    }

    /**
     * @return string
     */
    public function getResName(): string
    {
        return $this->resName;
    }

    /**
     * @param string $resName
     */
    public function setResName(string $resName): void
    {
        $this->resName = $resName;
    }

    /**
     * @return string
     */
    public function getChainId(): string
    {
        return $this->chainId;
    }

    /**
     * @param string $chainId
     */
    public function setChainId(string $chainId): void
    {
        $this->chainId = $chainId;
    }

    /**
     * @return int
     */
    public function getResSeq(): int
    {
        return $this->resSeq;
    }

    /**
     * @param int $resSeq
     */
    public function setResSeq(int $resSeq): void
    {
        $this->resSeq = $resSeq;
    }

    /**
     * @return string
     */
    public function getICode(): string
    {
        return $this->iCode;
    }

    /**
     * @param string $iCode
     */
    public function setICode(string $iCode): void
    {
        $this->iCode = $iCode;
    }

    /**
     * @return int
     */
    public function getModel(): int
    {
        return $this->model;
    }

    /**
     * @param int $model
     */
    public function setModel(int $model): void
    {
        $this->model = $model;
    }

    /**
     * @return float
     */
    public function getX(): float
    {
        return $this->x;
    }

    /**
     * @param float $x
     */
    public function setX(float $x): void
    {
        $this->x = $x;
    }

    /**
     * @return float
     */
    public function getY(): float
    {
        return $this->y;
    }

    /**
     * @param float $y
     */
    public function setY(float $y): void
    {
        $this->y = $y;
    }

    /**
     * @return float
     */
    public function getZ(): float
    {
        return $this->z;
    }

    /**
     * @param float $z
     */
    public function setZ(float $z): void
    {
        $this->z = $z;
    }

    /**
     * @return float
     */
    public function getOccupancy(): float
    {
        return $this->occupancy;
    }

    /**
     * @param float $occupancy
     */
    public function setOccupancy(float $occupancy): void
    {
        $this->occupancy = $occupancy;
    }

    /**
     * @return float
     */
    public function getTempFactor(): float
    {
        return $this->tempFactor;
    }

    /**
     * @param float $tempFactor
     */
    public function setTempFactor(float $tempFactor): void
    {
        $this->tempFactor = $tempFactor;
    }

    /**
     * @return string
     */
    public function getElement(): string
    {
        return $this->element;
    }

    /**
     * @param string $element
     */
    public function setElement(string $element): void
    {
        $this->element = $element;
    }
}
