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
use AppBundle\Entity\Database;

class DatabaseManager
{
    private $database;
    private $swissprot;
    private $genbank;
    public  $byteoff;

    /**
     * Constructor
     * @param ParseGenbankManager   $oGenbank
     * @param ParseSwissprotManager $oSwissprot
     */
    public function __construct(ParseGenbankManager $oGenbank, ParseSwissprotManager $oSwissprot) {
        $this->genbank      = $oGenbank;
        $this->swissprot    = $oSwissprot;
        $this->byteoff      = 0;
    }


    /**
     * Setting the database into the service
     * @param Database $database
     */
    public function setDatabase(Database $database)
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
        try {
            if($this->database->getSeqptr() < ($this->database->getSeqcount()-1)) {
                $this->database->setSeqptr($this->database->getSeqptr()+1);
            } else {
                $this->database->setEof(true);
            }
        } catch (Exception $ex) {
            throw new Exception($ex);
        }
    }


    /**
     * Retrieves all data from the specified sequence record and returns them in the 
     * form of a Seq object.
     * This method invokes one of several parser methods.
     * @return      Sequence    $oMySequence
     */
    public function fetch($seqid)
    {
        try {
            if ($this->database->getDataFn() == ""){
                throw new \Exception("Cannot invoke fetch() method from a closed object.");
            }

            if(is_file($this->database->getDataFn())) {
                $idx_r = $this->searchIdInIdx($this->database->getDataFn(), $seqid);
            } else {
                throw new \Exception("Unable to open ".$this->database->getDataFn());
            }

            if (!$idx_r) {
                return false;
            } else {
                $this->database->setSeqptr($idx_r[3]); // I got my id <3
            }
 
            $dir_r = $this->getFileInDirWithIndex($this->database->getDirFn(), $idx_r[3]);
            $fpseq = fopen($dir_r, "r");

            $this->fseekline($fpseq, $idx_r[2]);
            $flines = $this->line2r($fpseq);

            $this->lauchParsing($flines);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Creates .dir and .idx files
     * @return type
     * @throws Exception
     * @todo buffer more than 1 DB in the files
     */
    public function buffering()
    {
        try {
            $datafile = $this->database->getDatafile();
            if (count($datafile) == 0) {
                throw new Exception("Database not specified !");
            }

            if (file_exists($this->database->getDbname())) {
                $this->open($this->database->getDbname());
            } else {
                $fp = fopen($this->database->getDbname() . ".idx", "w+");
                $fpdir = fopen($this->database->getDbname() . ".dir", "w+");
                $this->open($this->database->getDbname());
                $temp_r = array();

                // Build our *.dir file
                $outline = "0 $datafile\n";
                fputs($fpdir, $outline);

                // Automatically create an index file containing info across all data files.
                $flines = file($datafile);
                $dbformat = $this->database->getDbformat();
                    
                while(list($lineno, $linestr) = each($flines)) {
                    if ($this->checkFormat($linestr, $dbformat)) {
                        $current_id = $this->get_entryid($flines, $linestr, $dbformat);
                        $outline = "$current_id 0 $lineno\n";
                        $temp_r[$current_id] = array($current_id, 0, $lineno);
                    }
                }

                // Build our *.idx array.
                $this->database->setSeqcount(count($temp_r));
                foreach($temp_r as $seqid => $line_r) {
                    $outline = $line_r[0] . " " . $line_r[1] . " " . $line_r[2] . "\n";
                    fputs($fp, $outline);
                }
            }
            fclose($fp);
            fclose($fpdir);
            return true;
        } catch (Exception $ex) {
            throw new Exception($ex);
        }
    }


    /**
     * Opens or prepares the SeqDB for processing.  Opposite of close().
     * @param type $dbname
     */
    public function open($dbname)
    {
        try {
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
        } catch (Exception $ex) {
            throw new Exception($ex);
        }
    }


    /**
     * Closes the SeqDB database after we're through using it.  Opposite of open() method.
     * Close simply assigns null values to attributes of the seqdb() object.
     * Methods like fetch would not function properly if these values are null.
     */
    public function close()
    { 
        try {
            $this->database->setDbname("");
            $this->database->setDataFn("");
            $this->database->setDirFn("");
            $this->database->setSeqptr(-1);
        } catch (Exception $ex) {
            throw new Exception($ex);
        }
    }


    /**
     * Lauches the sequencing
     * @param array $flines
     */
    private function lauchParsing($flines)
    {
        if ($this->database->getDbformat() == "GENBANK") {
            //$oMySequence = $this->genbank->parse_id($flines);
            dump("Pour le moment c'est cool !");
        } elseif ($this->database->getDbformat() == "SWISSPROT") {
            //$oMySequence = $this->swissprot->parse_swissprot($flines);
            dump("Pour le moment c'est cool !");
        }
        //return $oMySequence;
    }


    /**
     * Gets the seq file with its id
     * @param   string $file
     * @param   int $index
     * @return  string
     */
    private function getFileInDirWithIndex($file, $index)
    {
        $fichier = $file;
        $tabfich = file($fichier);
        foreach($tabfich as $ligne) {
            $aLine = explode(" ",$ligne);
            if($aLine[0] == $index) {
                return rtrim($aLine[1], "\n");
            }
        }
    }


    /**
     * Searches the provided id into the database file
     * @param   string $file
     * @param   string $searchfor
     * @return  array
     */
    private function searchIdInIdx($file, $searchfor)
    {
        $contents = file_get_contents($file);
        $pattern = preg_quote($searchfor, '/');
        $pattern = "/^.*$pattern.*\$/m";
        if(preg_match_all($pattern, $contents, $matches)){
            $results = [];
            $aMatches = explode(" ", $matches[0][0]);
            foreach($aMatches as $field) {
                if($field != "") {
                    $aResults[] = $field;
                }     
            }
            return $aResults;
        }
    }


    /**
     * Checks the format within the file
     * @param   string $linestr
     * @param   string $dbformat
     * @return  string
     * @throws  Exception
     */
    private function checkFormat($linestr, $dbformat)
    {
        try {
            if ($dbformat == "GENBANK") {
                $locus = substr($linestr,0,5);
                return ($locus == "LOCUS");
            } elseif ($dbformat == "SWISSPROT") {
                return (substr($linestr,0,2) == "ID");
            } 
        } catch (Exception $ex) {
            throw new Exception($ex);
        }
    }


    /**
     * gets the primary accession number of the sequence entry which we are
     * currently processing.  This uniquely identifies a sequence entry.
     * @param   string $flines
     * @param   string $linestr
     * @param   string $dbformat
     * @return  int
     */
    private function get_entryid(&$flines, $linestr, $dbformat)
    {
        if ($dbformat == "GENBANK") {
            return trim(substr($linestr, 12, 16));
        } elseif ($dbformat == "SWISSPROT") {
            list($lineno, $linestr) = each($flines);
            if (substr($linestr,0,2) == "AC") {
                $words = preg_split("/;/", intrim(substr($linestr,5)));
                prev($flines);
                return $words[0];
            }
        }
    }


    /**
     * Gets the byte offset (from beginning of file) of a particular line.  The file is
     * identified by $fp file pointer, while the line is identified by $lineno, which is zero-based.
     * @param   string $fp
     * @param   int $lineno
     * @return  void
     */
    private function fseekline($fp, $lineno)
    {
        $linectr = 0;
        fseek($fp, 0);
        while(!feof($fp)) {
            $linestr = fgets($fp,101);
            if ($linectr == $lineno) {
                fseek($fp, $this->byteoff);
                return $this->byteoff;
            }
            $linectr++;
            $this->byteoff = ftell($fp);
        }
    }


    /**
     * Copies the lines belonging to a single sequence entry into an array.
     * @param   type $fpseq
     * @return  boolean
     */
    private function line2r($fpseq)
    {
        $flines = array();
        while(1) {
            $linestr = fgets($fpseq, 101);
            $flines[] = $linestr;
            if(substr($linestr, 0, 2) == '//') {
                return $flines;
            }
        }
        return false;
    }
} 