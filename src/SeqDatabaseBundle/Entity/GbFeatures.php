<?php
/**
* Doctrine Entity GbFeatures
* Freely inspired by BioPHP's project biophp.org
* Created 23 march 2019
* Last modified 26 march 2019
*/
namespace SeqDatabaseBundle\Entity;

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
class GbFeatures
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
}