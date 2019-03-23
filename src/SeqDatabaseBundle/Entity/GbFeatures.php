<?php
/**
* Doctrine Entity GbFeatures
* Freely inspired by BioPHP's project biophp.org
* Created 23 march 2019
* Last modified 23 march 2019
*/
namespace SeqDatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GbFeatures
 * @package SeqDatabaseBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="gb_features")
 */
class GbFeatures
{
    /**
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = false,
     *     options = {"default":''}
     * )
     */
    private $primAcc;

    /**
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 15,
     *     nullable = false,
     *     options = {"default":''}
     * )
     */
    private $ftKey;

    /**
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 60,
     *     nullable = false,
     *     options = {"default":''}
     * )
     */
    private $ftQual;

    /**
     * @ORM\Column(
     *     type="text"
     * )
     */
    private $ftValue;

    /**
     * @return mixed
     */
    public function getPrimAcc()
    {
        return $this->primAcc;
    }

    /**
     * @param mixed $primAcc
     */
    public function setPrimAcc($primAcc)
    {
        $this->primAcc = $primAcc;
    }

    /**
     * @return mixed
     */
    public function getFtKey()
    {
        return $this->ftKey;
    }

    /**
     * @param mixed $ftKey
     */
    public function setFtKey($ftKey)
    {
        $this->ftKey = $ftKey;
    }

    /**
     * @return mixed
     */
    public function getFtQual()
    {
        return $this->ftQual;
    }

    /**
     * @param mixed $ftQual
     */
    public function setFtQual($ftQual)
    {
        $this->ftQual = $ftQual;
    }

    /**
     * @return mixed
     */
    public function getFtValue()
    {
        return $this->ftValue;
    }

    /**
     * @param mixed $ftValue
     */
    public function setFtValue($ftValue)
    {
        $this->ftValue = $ftValue;
    }
}
