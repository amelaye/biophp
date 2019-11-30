<?php
/**
* Doctrine Entity GbFeatures
* Freely inspired by BioPHP's project biophp.org
* Created 23 march 2019
* Last modified 13 november 2019
*/
namespace AppBundle\Entity\Sequencing;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GbFeatures
 * @package SeqDatabaseBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(
 *     name = "gb_features",
 *     uniqueConstraints = {
 *        @ORM\UniqueConstraint(
 *            name = "prim_acc",
 *            columns = {"prim_acc", "ft_key", "ft_qual"}
 *        )
 *     }
 * )
 */
class Features
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity = "SeqDatabaseBundle\Entity\Sequence")
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
    public function getPrimAcc()
    {
        return $this->primAcc;
    }

    /**
     * @param string $primAcc
     */
    public function setPrimAcc($primAcc)
    {
        $this->primAcc = $primAcc;
    }

    /**
     * @return string
     */
    public function getFtKey()
    {
        return $this->ftKey;
    }

    /**
     * @param string $ftKey
     */
    public function setFtKey($ftKey)
    {
        $this->ftKey = $ftKey;
    }

    /**
     * @return string
     */
    public function getFtQual()
    {
        return $this->ftQual;
    }

    /**
     * @param string $ftQual
     */
    public function setFtQual($ftQual)
    {
        $this->ftQual = $ftQual;
    }

    /**
     * @return string
     */
    public function getFtValue()
    {
        return $this->ftValue;
    }

    /**
     * @param string $ftValue
     */
    public function setFtValue($ftValue)
    {
        $this->ftValue = $ftValue;
    }

    /**
     * @return int
     */
    public function getFtFrom(): int
    {
        return $this->ftFrom;
    }

    /**
     * @param int $ftFrom
     */
    public function setFtFrom(int $ftFrom): void
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
    public function setFtTo(int $ftTo): void
    {
        $this->ftTo = $ftTo;
    }

    /**
     * @return string
     */
    public function getFtDesc(): string
    {
        return $this->ftDesc;
    }

    /**
     * @param string $ftDesc
     */
    public function setFtDesc(string $ftDesc): void
    {
        $this->ftDesc = $ftDesc;
    }
}
