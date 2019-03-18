<?php
/**
 * Entity used by form FastaUploaderType
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 18 march 2019
 * Last modified 18 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class FastaUploader
{
    /**
     * @Assert\File(
     *     maxSize = "100000k",
     *     mimeTypes = { "text/plain" },
     *     mimeTypesMessage = "Please upload a valid TXT"
     * )
     */
    private $fasta;

    public function getFasta()
    {
        return $this->fasta;
    }

    public function setFasta($fasta)
    {
        $this->fasta = $fasta;
    }
}