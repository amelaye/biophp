<?php
/**
 * Doctrine Entity Keywords
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 23 march 2019
 */
namespace SeqDatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Keywords
 * @package SeqDatabaseBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(
 *     name = "keywords",
 *     uniqueConstraints = {
 *        @ORM\UniqueConstraint(
 *            name = "prim_acc",
 *            columns = {"prim_acc", "keywords"}
 *        )
 *     }
 * )
 */
class Keywords
{
    /**
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = false
     * )
     */
    private $primAcc;

    /**
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 80,
     *     nullable = false
     * )
     */
    private $keywords;

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
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }
}