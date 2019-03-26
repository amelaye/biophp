<?php
/**
 * Doctrine Entity Keywords
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 26 march 2019
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
     * @var string
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity = "SeqDatabaseBundle\Entity\Sequence")
     * @ORM\JoinColumn(
     *     name = "prim_acc",
     *     referencedColumnName = "prim_acc"
     * )
     */
    private $primAcc;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 80,
     *     nullable = false
     * )
     */
    private $keywords;

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
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }
}