<?php
/**
 * Doctrine Entity Accession
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 23 november 2019
 */
namespace AppBundle\Entity\Sequencing;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Accession
 * @package AppBundle\Entity\Sequencing
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(
 *     name = "accession",
 *     uniqueConstraints = {
 *        @ORM\UniqueConstraint(
 *            name = "prim_acc",
 *            columns = {"prim_acc", "accession"})
 *     }
 * )
 */
class Accession
{

    /**
     * @var string
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity = "AppBundle\Entity\Sequencing\Sequence")
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
     *     length = 8,
     *     nullable = false
     *     )
     */
    private $accession;

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
    public function getAccession()
    {
        return $this->accession;
    }

    /**
     * @param string $accession
     */
    public function setAccession($accession)
    {
        $this->accession = $accession;
    }
}