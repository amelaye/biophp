<?php
/**
 * Doctrine Entity SrcForm
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 23 march 2019
 */
namespace SeqDatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SrcForm
 * @package SeqDatabaseBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(
 *     name = "src_form",
 *     uniqueConstraints = {
 *        @ORM\UniqueConstraint(
 *            name = "prim_acc",
 *            columns = {"prim_acc"}
 *        )
 *     }
 * )
 */
class SrcForm
{
    /**
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = false,
     *     options = {"default":0}
     * )
     */
    private $primAcc;

    /**
     * @ORM\Column(
     *     type="text"
     * )
     */
    private $entry;

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
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param mixed $entry
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
    }
}