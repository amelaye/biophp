<?php
/**
 * Global database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 30 november 2019
 */
namespace AppBundle\Service\IO;

use AppBundle\Entity\Sequencing\GbSequence;
use AppBundle\Entity\Sequencing\Sequence;
use AppBundle\Entity\Sequencing\SrcForm;
use AppBundle\Traits\FormatsTrait;

/**
 * Class ParseDbAbstractManager
 * @package AppBundle\Service\IO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class ParseDbAbstractManager implements ParseDatabaseInterface
{
    use FormatsTrait;

    /**
     * @var array
     */
    protected $accession;

    /**
     * @var Sequence
     */
    protected $sequence;

    /**
     * @var array
     */
    protected $authors;

    /**
     * @var array
     */
    protected $features;

    /**
     * @var array
     */
    protected $keywords;

    /**
     * @var array
     */
    protected $references;

    /**
     * @var SrcForm
     */
    protected $srcForm;

    /**
     * @var GbSequence
     */
    protected $gbSequence;

    /**
     * @var array
     */
    protected $spDatabank;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->accession    = []; // array of Accessions();
        $this->sequence     = new Sequence();
        $this->authors      = []; // array of Authors()
        $this->gbSequence   = new GbSequence();
        $this->features     = []; // array of Features();
        $this->keywords     = []; // array of Keywords();
        $this->references   = []; // array of References();
        $this->srcForm      = new SrcForm();
        $this->spDatabank   = []; // array of SpDatabank();
    }

    /**
     * @return array
     */
    public function getAccession()
    {
        return $this->accession;
    }

    /**
     * @return Sequence
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @return array
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * @return GbSequence
     */
    public function getGbSequence()
    {
        return $this->gbSequence;
    }

    /**
     * @return array
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @return array
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @return SrcForm
     */
    public function getSrcForm()
    {
        return $this->srcForm;
    }

    /**
     * @return array
     */
    public function getSpDatabank(): array
    {
        return $this->spDatabank;
    }

    /**
     * @param array $spDatabank
     */
    public function setSpDatabank(array $spDatabank): void
    {
        $this->spDatabank = $spDatabank;
    }

    /**
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws \Exception
     */
    public function parseDataFile($aFlines) {}
}