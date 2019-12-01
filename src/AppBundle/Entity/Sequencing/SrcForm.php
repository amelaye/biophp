<?php
/**
 * Doctrine Entity SrcForm
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 13 november 2019
 */
namespace AppBundle\Entity\Sequencing;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SrcForm
 * @package AppBundle\Entity\Sequencing
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
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
     * @var string
     * @ORM\Id
     * @ORM\OneToOne(targetEntity = "AppBundle\Entity\Sequencing\Sequence")
     * @ORM\JoinColumn(
     *     name = "prim_acc",
     *     referencedColumnName = "prim_acc"
     * )
     */
    private $primAcc;

    /**
     * @var string
     * @ORM\Column(
     *     type="text"
     * )
     */
    private $entry;

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
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param string $entry
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
    }
}