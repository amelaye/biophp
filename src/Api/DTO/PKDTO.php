<?php
/**
 * Database of elements - PK Values
 * Inspired by BioPHP's project biophp.org
 * Created 20 december 2019
 * Last modified 20 december 2019
 */
namespace AppBundle\Api\DTO;

/**
 * Database of elements - PK Values
 * @package AppBundle\Api\DTO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PKDTO
{
    /**
     * @var     string       Id of the row (EMBOSS ...)
     */
    private $id;

    /**
     * @var     float
     */
    private $nTerminus;

    /**
     * @var     float
     */
    private $k;

    /**
     * @var     float
     */
    private $r;

    /**
     * @var     float
     */
    private $h;

    /**
     * @var     float
     */
    private $cTerminus;

    /**
     * @var     float
     */
    private $d;

    /**
     * @var     float
     */
    private $e;

    /**
     * @var     float
     */
    private $c;

    /**
     * @var     float
     */
    private $y;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }


    /**
     * @return float
     */
    public function getNTerminus(): float
    {
        return $this->nTerminus;
    }

    /**
     * @param float $nTerminus
     */
    public function setNTerminus(float $nTerminus): void
    {
        $this->nTerminus = $nTerminus;
    }

    /**
     * @return float
     */
    public function getK(): float
    {
        return $this->k;
    }

    /**
     * @param float $k
     */
    public function setK(float $k): void
    {
        $this->k = $k;
    }

    /**
     * @return float
     */
    public function getR(): float
    {
        return $this->r;
    }

    /**
     * @param float $r
     */
    public function setR(float $r): void
    {
        $this->r = $r;
    }

    /**
     * @return float
     */
    public function getH(): float
    {
        return $this->h;
    }

    /**
     * @param float $h
     */
    public function setH(float $h): void
    {
        $this->h = $h;
    }

    /**
     * @return float
     */
    public function getCTerminus(): float
    {
        return $this->cTerminus;
    }

    /**
     * @param float $cTerminus
     */
    public function setCTerminus(float $cTerminus): void
    {
        $this->cTerminus = $cTerminus;
    }

    /**
     * @return float
     */
    public function getD(): float
    {
        return $this->d;
    }

    /**
     * @param float $d
     */
    public function setD(float $d): void
    {
        $this->d = $d;
    }

    /**
     * @return float
     */
    public function getE(): float
    {
        return $this->e;
    }

    /**
     * @param float $e
     */
    public function setE(float $e): void
    {
        $this->e = $e;
    }

    /**
     * @return float
     */
    public function getC(): float
    {
        return $this->c;
    }

    /**
     * @param float $c
     */
    public function setC(float $c): void
    {
        $this->c = $c;
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
}