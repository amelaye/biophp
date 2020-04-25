<?php
/**
 * Doctrine Entity GbFeatures
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 18 january 2020
 */
namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GbFeatures
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(
 *     name = "feature",
 *     uniqueConstraints = {
 *        @ORM\UniqueConstraint(
 *            name = "prim_acc",
 *            columns = {"prim_acc", "ft_key", "ft_qual"}
 *        )
 *     }
 * )
 */
class Feature
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity = "Amelaye\BioPHP\Domain\Sequence\Entity\Reference")
     * @ORM\JoinColumn(
     *     name = "prim_acc",
     *     referencedColumnName="prim_acc"
     * )
     */
    private $primAcc;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 15,
     *     nullable = false
     * )
     */
    private $ftKey;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = true
     * )
     */
    private $ftFrom;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = true
     * )
     */
    private $ftTo;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 60,
     *     nullable = false
     * )
     */
    private $ftQual;

    /**
     * @var string
     * @ORM\Column(
     *     type="text"
     * )
     */
    private $ftValue;

    /**
     * @var string
     * @ORM\Column(
     *     type="text"
     * )
     */
    private $ftDesc;

    /**
     * @return string
     */
    public function getPrimAcc() : string
    {
        return $this->primAcc;
    }

    /**
     * @param string $primAcc
     */
    public function setPrimAcc(string $primAcc) : void
    {
        $this->primAcc = $primAcc;
    }

    /**
     * @return string
     */
    public function getFtKey() : string
    {
        return $this->ftKey;
    }

    /**
     * @param string $ftKey
     */
    public function setFtKey(string $ftKey) : void
    {
        $this->ftKey = $ftKey;
    }

    /**
     * @return string
     */
    public function getFtQual() : string
    {
        return $this->ftQual;
    }

    /**
     * @param string $ftQual
     */
    public function setFtQual(string $ftQual) : void
    {
        $this->ftQual = $ftQual;
    }

    /**
     * @return string
     */
    public function getFtValue() : string
    {
        return $this->ftValue;
    }

    /**
     * @param string $ftValue
     */
    public function setFtValue(string $ftValue) : void
    {
        $this->ftValue = $ftValue;
    }

    /**
     * @return int
     */
    public function getFtFrom() : int
    {
        return $this->ftFrom;
    }

    /**
     * @param int $ftFrom
     */
    public function setFtFrom(int $ftFrom) : void
    {
        $this->ftFrom = $ftFrom;
    }

    /**
     * @return int
     */
    public function getFtTo(): int
    {
        return $this->ftTo;
    }

    /**
     * @param int $ftTo
     */
    public function setFtTo(int $ftTo) : void
    {
        $this->ftTo = $ftTo;
    }

    /**
     * @return string
     */
    public function getFtDesc() : string
    {
        return $this->ftDesc;
    }

    /**
     * @param string $ftDesc
     */
    public function setFtDesc(string $ftDesc) : void
    {
        $this->ftDesc = $ftDesc;
    }
}
