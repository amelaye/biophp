<?php
/**
 * Biological Databases Managing
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 25 october 2019
 */
namespace AppBundle\Service\IO;


use AppBundle\Interfaces\RecordingOnLocalDb;
use AppBundle\Traits\FormatsTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use SeqDatabaseBundle\Entity\Collection;
use SeqDatabaseBundle\Entity\CollectionElement;
use AppBundle\Entity\Sequence;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


/**
 * This class does many things like create
 * and/or read a collection of database's index files, initialize certain SeqDB properties, etc.
 * Syntax: $seqdb = new seqdb($dbname, $dbformat, $file1, $file2, ...);
 * Behavior: if $dbname exists and user gave no specific values for $file1, $file2, ...
 * then seqdb() object USES/OPENS existing database (index files).
 * if $dbname exists and user gave specific values for $file1, $file2, ...
 * then seqdb() object OVERWRITES existing database (index files).
 * if $dbname does not exist, then seqdb() object CREATES new database.
 * even if $file1, $file2, ... are not specified.
 * We provide the create() method to explicitly create a new database.
 * We provide the use() or open() method to explicitly use an existing database.
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class DatabaseManager implements RecordingOnLocalDb
{
    use FormatsTrait;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ParseGenbankManager
     */
    protected $parseGenbankManager;

    /**
     * @var ParseSwissprotManager
     */
    protected $parseSwissprotManager;


    /**
     * DatabaseManager constructor.
     * @param EntityManagerInterface     $em                         Entity Manager, for Doctrine
     * @param ParseGenbankManager        $parseGenbankManager        Service for GENBANK data
     * @param ParseSwissprotManager      $parseSwissprotManager      Service for SwissProt data
     */
    public function __construct(
        EntityManagerInterface $em,
        ParseGenbankManager $parseGenbankManager,
        ParseSwissprotManager $parseSwissprotManager
    ) {
        $this->em = $em;
        $this->parseGenbankManager = $parseGenbankManager;
        $this->parseSwissprotManager = $parseSwissprotManager;
    }


    /**
     * Retrieves all data from the specified sequence record and returns them in the
     * form of a Seq object.  This method invokes one of several parser methods.
     * @param       string          $sSeqId     The id of the seq obj.
     * @return      Sequence|bool
     * @throws      \Exception
     */
    public function fetch($sSeqId)
    {
        try {
            $collectionDB  = $this->em->getRepository(CollectionElement::class)->findOneBy(['idElement' => $sSeqId]);

            if (empty($collectionDB)) {
                return false;
            }

            if(!is_file($collectionDB->getFileName())) {
                throw new FileException("The file ".$collectionDB->getFileName()." doesn't exist !");
            }

            $fpSeq = fopen($collectionDB->getFileName(), "r");
            $aFlines = $this->line2r($fpSeq);

            if ($collectionDB->getDbFormat() == "GENBANK") {
                $oSequence = $this->parseGenbankManager->parseDataFile($aFlines);
            }
            elseif ($collectionDB->getDbFormat() == "SWISSPROT") {
                $oSequence = $this->parseSwissprotManager->parseDataFile($aFlines);
            }
            else {
                throw new \Exception("Unknown database format ! ");
            }
            return $oSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Records the new elements of a collection, reads a collection
     *  db exists   fileX args   ACTION
     *     Y            Y        create
     *     Y            N        use
     *     N            Y        create
     *     N            N        create
     * @throws \Exception
     */
    public function recording()
    {
        try {
            $args = func_get_args();

            $dbname = $args[0];
            $dbformat = strtoupper($args[1]);

            if (strlen($dbformat) == 0) {
                $dbformat = "GENBANK";
            }

            $datafile = array();
            for ($i = 2; $i < count($args); $i++) {
                $datafile[] = $args[$i];
            }

            $collection = new Collection();
            $collection->setNomCollection($dbname);

            $collectionExists = $this->em->getRepository(Collection::class)
                ->findOneBy(['nomCollection' => $dbname]);

            // if user provided specific values for $file1, $file2, ... parameters.
            if ((empty($collectionExists)) and (count($datafile) > 0)) {
                // For now, assume USING/OPENING a database is to be done in READ ONLY MODE.
                $this->em->persist($collection);
                $this->em->flush();
            } else {
                $collection = $collectionExists;
            }

            // if user did not provide any datafile name.
            if (count($datafile) == 0) {
                throw new \Exception("No files provided !");
            }

            $temp_r = array();

            foreach($datafile as $fileno => $filename) {
                // Automatically create an index file containing info across all data files.
                $flines = file($filename);

                foreach($flines as $lineno => $linestr) {
                    if ($this->atEntrystart($linestr, $dbformat)) {
                        $current_id =  $this->getEntryid($flines, $linestr, $dbformat);
                        $temp_r[$current_id] = array(
                            "id_element" => $current_id,
                            "filename" => $filename,
                            "dbformat" => $dbformat,
                            "line_no" => $lineno
                        );
                    }
                }
            }
            $this->seqcount = count($temp_r);

            foreach($temp_r as $seqid => $line_r) {
                // Check if the file already exists
                $collectionElementExists = $this->em->getRepository(CollectionElement::class)
                    ->findOneBy(['fileName' => $line_r["filename"]]);

                if(empty($collectionElementExists)) {
                    $collectionElement = new CollectionElement();
                    $collectionElement->setIdElement($line_r["id_element"]);
                    $collectionElement->setCollection($collection);
                    $collectionElement->setFileName($line_r["filename"]);
                    $collectionElement->setSeqCount(count($temp_r));
                    $collectionElement->setLineNo($line_r["line_no"]);
                    $collectionElement->setDbFormat($line_r["dbformat"]);

                    $this->em->persist($collectionElement);
                    $this->em->flush();
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Tests if the file pointer is at the start of a new sequence entry.
     * @param       string      $linestr        The line to analyze
     * @param       string      $dbformat       Original DB format (Swissprot, Genbank)
     * @return      bool
     * @throws      \Exception
     */
    private function atEntrystart($linestr, $dbformat)
    {
        try {
            if ($dbformat == "GENBANK") {
                return (substr($linestr,0,5) == "LOCUS");
            } elseif ($dbformat == "SWISSPROT") {
                return (substr($linestr,0,2) == "ID");
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Gets the primary accession number of the sequence entry which we are
     * currently processing.  This uniquely identifies a sequence entry.
     * @param       array       $flines     Buffed file as array
     * @param       string      $linestr    Current line
     * @param       string      $dbformat   Original DB format (Swissprot, Genbank)
     * @return      string
     * @throws      \Exception
     */
    private function getEntryid(&$flines, $linestr, $dbformat)
    {
        try {
            if ($dbformat == "GENBANK") {
                $locus = preg_split("/\s+/", trim($linestr));
                $entyId = $locus[1];
                return trim($entyId);
            } elseif ($dbformat == "SWISSPROT") {
                foreach ($flines as $lineno => $linestr) {
                    if (substr($linestr,0,2) == "AC") {
                        $words = preg_split("/;/", $this->intrim(substr($linestr,5)));
                        prev($flines);
                        return $words[0];
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Copies the lines belonging to a single sequence entry into an array.
     * @param   $fpseq
     * @return  array|bool
     * @throws  \Exception
     */
    private function line2r($fpseq)
    {
        try {
            $flines = array();
            while(1) {
                $linestr = fgets($fpseq, 101);
                $flines[] = $linestr;
                if (substr($linestr, 0, 2) == '//') {
                    return $flines;
                }
            }
            return false;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
} 