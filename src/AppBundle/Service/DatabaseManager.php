<?php
/**
 * Database Managing
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 15 february 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Sequence;
use AppBundle\Service\ParseGenbankManager;
use AppBundle\Service\ParseSwissprotManager;

class DatabaseManager
{
    private $database;
    private $swissprot;
    private $genbank;

    public function __construct(ParseGenbankManager $oGenbank, ParseSwissprotManager $oSwissprot) {
        $this->genbank      = $oGenbank;
        $this->swissprot    = $oSwissprot;
    }
    
    public function setDatabase($database)
    {
        $this->database     = $database;
    }
    
    /**
     * We need the functions bof() and eof() to determine if we've reached the end of
     * file or not.
     * Two ways of doing this: 1) examine value of seqptr, or 2) maintain boolean variables eof and bof
     * first() positions the sequence pointer (i.e. the seqptr property of a Seq object) to 
     * the first sequence in a database (SeqDB object).
     */
    public function first()
    {
        $this->database->setSeqptr(0);
    }


    /**
     * Positions the sequence pointer (i.e. the seqptr property of a Seq object) to 
     * the last sequence in a database (SeqDB object).
     */
    public function last()
    {
        $this->database->setSeqptr($this->database->getSeqcount() - 1);
    }


    /**
     * (short for previous) positions the sequence pointer (i.e. the seqptr property of
     * a Seq object) to the sequence that comes before the current sequence.  
     */
    public function prev()
    {
        if($this->database->getSeqptr() > 0) {
            $this->database->setSeqptr($this->database->getSeqptr()-1);
        } else {
            $this->database->setBof(true);
        }
    }

    /**
     * Positions the sequence pointer (i.e. the seqptr property of a Seq object) to the
     * sequence that comes after the current sequence.
     */
    public function next()
    {
        if($this->database->getSeqptr() < ($this->database->getSeqcount()-1)) {
            $this->database->setSeqptr($this->database->getSeqptr()+1);
        } else {
            $this->database->setEof(true);
        }
    }


    /**
     * Retrieves all data from the specified sequence record and returns them in the 
     * form of a Seq object.  This method invokes one of several parser methods.
     * @return      Sequence    $oMySequence
     */
    public function fetch()
    {
        if ($this->database->getDataFn() == ""){
            throw new \Exception("Cannot invoke fetch() method from a closed object.");
        }
        @$seqid = func_get_arg(0);

        // IDX and DIR files remain open for the duration of the FETCH() method.
        $fp = fopen($this->database->getDataFn(), "r");
        $fpdir = fopen($this->database->getDirFn(), "r");

        if ($seqid) {
            $idx_r = $this->bsrch_tabfile($fp, 0, $seqid);
            if (!$idx_r) {
                return false;
            } else {
                $this->database->setSeqptr($idx_r[3]);
            }
        } else {
            // For now, SEQPTR determines CURRENT SEQUENCE ID.  Alternative is to track curr line.
            fseekline($fp, $this->database->getSeqptr());
            $idx_r = preg_split("/\s+/", trim(fgets($fp, 81)));
        }
        $dir_r = $this->bsrch_tabfile($fpdir, 0, $idx_r[1]);
        $fpseq = fopen($dir_r[1], "r");
        fseekline($fpseq, $idx_r[2]);
        $flines = line2r($fpseq);

        if ($this->databse->getDbformat() == "GENBANK") {
            $oMySequence = $this->genbank->parse_id($flines);
        } elseif ($this->databse->getDbformat() == "SWISSPROT") {
            $oMySequence = $this->swissprot->parse_swissprot($flines);
        }

        fclose($fp);
        fclose($fpdir);
        fclose($fpseq);

        return $oMySequence;
    }


    /**
     * Opens or prepares the SeqDB for processing.  Opposite of close().
     * @param type $dbname
     */
    public function open($dbname)
    {
        if (!file_exists($dbname . ".idx")) {
            throw new \Exception("ERROR: Index file $dbname.IDX does not exist!");
        }

        if (!file_exists($dbname . ".dir")) {
            throw new \Exception("ERROR: Index file $dbname.DIR does not exist!");
        }

        $this->database->setDbname($dbname);
        $this->database->setDataFn($dbname . ".idx");
        $this->database->setDirFn($dbname . ".dir");
        $this->database->setSeqptr(0);
    }


    /**
     * Closes the SeqDB database after we're through using it.  Opposite of open() method.
     */
    public function close()
    { 
        // Close simply assigns null values to attributes of the seqdb() object.
        // Methods like fetch would not function properly if these values are null.
        $this->database->setDbname("");
        $this->database->setDataFn("");
        $this->database->setDirFn("");
        $this->database->setSeqptr(-1);
    }
    
    /**
     * Searches for a particular sequence id ($seqid) within an *.IDX file
     * (identified by $fp file pointer), and returns data located in its $col-th column.
     * @param type $fp
     * @param type $col
     * @param type $seqid
     * @return boolean
     */
    private function bsrch_tabfile($fp, $col, $seqid)
    {
        $linectr = 0;
        fseek($fp, 0);
        while(!feof($fp)) {
            fgets($fp, 41);
            $linectr++;
        }
        $lastline = $linectr;
        rewind($fp);

        if (!$fp) {
            throw new \Exception("CANT OPEN FILE");
        }

        $searchspace = $lastline;
        $floor = 0;
        $ceiling = $lastline - 1;

        while(1) {
            $offset = ((int) ($searchspace/2));
            $lineno = $floor + $offset;

            fseekline($fp, $lineno);
            $word = preg_split("/\s+/", trim(fgets($fp,81)));
            if ($word[$col] == $seqid) {
                $word[] = $lineno;
                return $word;
            } elseif ($seqid > $word[$col]) {
                $floor = $lineno + 1;
                $searchspace = $ceiling - $floor + 1;
                if ($searchspace <= 0) {
                    return FALSE;
                }
            } else {
                $ceiling = $lineno - 1;
                $searchspace = $ceiling - $floor + 1;
                if ($searchspace <= 0) {
                    return false;
                }
            }
        }
    }
} 