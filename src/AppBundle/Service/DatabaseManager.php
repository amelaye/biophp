<?php
/**
 * Database Managing
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 10 april 2019
 */
namespace AppBundle\Service;


use Doctrine\ORM\EntityManager;
use SeqDatabaseBundle\Entity\Collection;
use SeqDatabaseBundle\Entity\CollectionElement;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use AppBundle\Entity\Sequence;
use AppBundle\Service\ParseGenbankManager;
use AppBundle\Service\ParseSwissprotManager;


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
 */
class DatabaseManager
{
    var $dbname;
    var $data_fn;
    var $data_fp;
    var $dir_fn;
    var $dir_fp;
    var $seqptr;
    var $seqcount;
    var $dbformat;
    var $bof;
    var $eof;

    protected $em;
    protected $parseGenbankManager;
    protected $parseSwissprotManager;

    public function __construct(
        EntityManager $em,
        ParseGenbankManager $parseGenbankManager,
        ParseSwissprotManager $parseSwissprotManager
    ) {
        $this->em = $em;
        $this->parseGenbankManager = $parseGenbankManager;
        $this->parseSwissprotManager = $parseSwissprotManager;
    }


// fetch() retrieves all data from the specified sequence record and returns them in the
// form of a Seq object.  This method invokes one of several parser methods.
    function fetch()
    {
        if ($this->data_fn == "") die("Cannot invoke fetch() method from a closed object.");
        $seqid = func_get_arg(0);
/*
        // IDX and DIR files remain open for the duration of the FETCH() method.
        $fp = fopen($this->data_fn, "r");
        $fpdir = fopen($this->dir_fn, "r");

        if ($seqid != FALSE)
        {
            $idx_r = $this->bsrch_tabfile($fp, 0, $seqid);
            if ($idx_r == FALSE) return FALSE;
            else $this->seqptr = $idx_r[3];
        }
        else
        {
            // For now, SEQPTR determines CURRENT SEQUENCE ID.  Alternative is to track curr line.
            $this->fseekline($fp, $this->seqptr);
            $idx_r = preg_split("/\s+/", trim(fgets($fp, 81)));
        }
        $dir_r = $this->bsrch_tabfile($fpdir, 0, $idx_r[1]);

        $fpseq = fopen($dir_r[1], "r");
        $this->fseekline($fpseq, $idx_r[2]);

        $flines = $this->line2r($fpseq);

        $myseq = new Sequence();
        if ($this->dbformat == "GENBANK")
            dump("C'est du genbank");
            //$myseq = $this->parse_id($flines);
        elseif ($this->dbformat == "SWISSPROT")
            dump("C'est du swissprot");
            //$myseq = $this->parse_swissprot($flines);

        fclose($fp);
        fclose($fpdir);
        fclose($fpseq);

        return $myseq;*/
return true;
    }


    /**
     * Records the new elements of a collection, reads a collection
     * @throws \Exception
     */
    public function buffering()
    {
        try {
            // Get all the arguments passed to this function.
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

            /* db exists   fileX args   ACTION   TESTED
                    Y            Y        create   okay
                    Y            N        use
                    N            Y        create    okay
                    N            N        create    okay
            */
            $collection = new Collection();
            $collection->setNomCollection($dbname);

            $collectionExists = $this->em->getRepository(Collection::class)
                ->findBy(['nomCollection' => $dbname]);

            // if user provided specific values for $file1, $file2, ... parameters.
            if ((empty($collectionExists)) and (count($datafile) > 0)) {
                // For now, assume USING/OPENING a database is to be done in READ ONLY MODE.
                $this->em->persist($collection);
                $this->em->flush();
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
    public function atEntrystart($linestr, $dbformat)
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
    public function getEntryid(&$flines, $linestr, $dbformat)
    {
        try {
            if ($dbformat == "GENBANK") {
                $locus = preg_split("/\s+/", trim($linestr));
                $entyId = $locus[1];
                return trim($entyId);
            } elseif ($dbformat == "SWISSPROT") {
                foreach ($flines as $lineno => $linestr) {
                    if (substr($linestr,0,2) == "AC") {
                        $words = preg_split("/;/", intrim(substr($linestr,5)));
                        prev($flines);
                        return $words[0];
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
} 