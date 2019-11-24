<?php
/**
 * Global database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 24 november 2019
 */
namespace AppBundle\Service\IO;

use AppBundle\Entity\Sequencing\GbSequence;
use AppBundle\Entity\Sequencing\Sequence;
use AppBundle\Entity\Sequencing\SrcForm;

/**
 * Class ParseDbAbstractManager
 * @package AppBundle\Service\IO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class ParseDbAbstractManager implements ParseDatabaseInterface
{
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
    protected $gbFeatures;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->accession    = []; // array of Accessions();
        $this->sequence     = new Sequence();
        $this->authors      = []; // array of Authors()
        $this->gbSequence   = new GbSequence();
        $this->gbFeatures   = []; // array of GbFeatures();
        $this->keywords     = []; // array of Keywords();
        $this->references   = []; // array of Keywords();
        $this->srcForm      = new SrcForm();
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
    public function getGbFeatures()
    {
        return $this->gbFeatures;
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
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws \Exception
     */
    public function parseDataFile($aFlines) {}
}