<?php
/**
 * Doctrine Entity Swissprot databank
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 november 2019
 * Last modified 18 january 2020
 */
namespace AppBundle\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SrcForm
 * @package AppBundle\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(
 *     name = "sp_databank",
 *     uniqueConstraints = {
 *        @ORM\UniqueConstraint(
 *            name = "prim_acc",
 *            columns = {"prim_acc"}
 *        )
 *     }
 * )
 */
class SpDatabank
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\OneToOne(targetEntity = "AppBundle\Domain\Sequence\Entity\Sequence")
     * @ORM\JoinColumn(
     *     name = "prim_acc",
     *     referencedColumnName = "prim_acc"
     * )
     */
    private $primAcc;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 6,
     *     nullable = true
     * )
     */
    private $dbName;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 6,
     *     nullable = true
     * )
     */
    private $pid1;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 6,
     *     nullable = true
     * )
     */
    private $pid2;

    /**
     * @return string
     */
    public function getPrimAcc(): string
    {
        return $this->primAcc;
    }

    /**
     * @param string $primAcc
     */
    public function setPrimAcc(string $primAcc): void
    {
        $this->primAcc = $primAcc;
    }

    /**
     * @return string
     */
    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * @param string $dbName
     */
    public function setDbName(string $dbName): void
    {
        $this->dbName = $dbName;
    }

    /**
     * @return string
     */
    public function getPid1(): string
    {
        return $this->pid1;
    }

    /**
     * @param string $pid1
     */
    public function setPid1(string $pid1): void
    {
        $this->pid1 = $pid1;
    }

    /**
     * @return string
     */
    public function getPid2(): string
    {
        return $this->pid2;
    }

    /**
     * @param string $pid2
     */
    public function setPid2(string $pid2): void
    {
        $this->pid2 = $pid2;
    }
}