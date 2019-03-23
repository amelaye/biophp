<?php
/**
 * Doctrine Entity Accession
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 23 march 2019
 */
namespace SeqDatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Accession
 * @package SeqDatabaseBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="accession")
 */
class Accession
{

    /**
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = false,
     *     options = {"default":''}
     *     )
     */
    private $primAcc;

    /**
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = false,
     *     options = {"default":''}
     *     )
     */
    private $accession;

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
    public function getAccession()
    {
        return $this->accession;
    }

    /**
     * @param mixed $accession
     */
    public function setAccession($accession)
    {
        $this->accession = $accession;
    }
}