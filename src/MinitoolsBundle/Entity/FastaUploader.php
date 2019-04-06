<?php
/**
 * Entity used by form FastaUploaderType
 * Freely inspired by BioPHP's project biophp.org
 * Created 18 march 2019
 * Last modified 18 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FastaUploader
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class FastaUploader
{
    /**
     * @var array
     * @Assert\File(
     *     maxSize = "100000k",
     *     mimeTypes = { "text/plain" },
     *     mimeTypesMessage = "Please upload a valid TXT"
     * )
     */
    private $fasta;

    /**
     * @return array
     */
    public function getFasta()
    {
        return $this->fasta;
    }

    /**
     * @param array $fasta
     */
    public function setFasta($fasta)
    {
        $this->fasta = $fasta;
    }
}