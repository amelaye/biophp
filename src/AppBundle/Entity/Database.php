<?php
/**
 * Database Entity
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
class Database {

    /**
     * @var string
     */
    private $dbname;

    /**
     * @var string
     */
    private $data_fn;
    private $data_fp;

    /**
     * @var string
     */
    private $dir_fn;
    private $dir_fp;

    /**
     * @var int
     */
    private $seqptr;

    /**
     * @var int
     */
    private $seqcount;
    private $dbformat;

    /**
     * @var int
     */
    private $bof;

    /**
     * @var int
     */
    private $eof;


    /**
     * 
     * SeqDB() is the constructor method for the SeqDB class.  It does many things like create
     * and/or read a database's index files, initialize certain SeqDB properties, etc.
     * Syntax: $seqdb = new seqdb($dbname, $dbformat, $file1, $file2, ...);
     * Behavior: if $dbname exists and user gave no specific values for $file1, $file2,
     * then seqdb() object USES/OPENS existing database (index files)
     * if $dbname exists and user gave specific values for $file1, $file2,
     * then seqdb() object OVERWRITES existing database (index files).
     * if $dbname does not exist, then seqdb() object CREATES new database.
     * even if $file1, $file2, ... are not specified.
     * We provide the create() method to explicitly create a new database.
     * We provide the use() or open() method to explicitly use an existing database.
     * 
     * @return type
     */
    function __construct()
    {
        // Get all the arguments passed to this function.
        $args = func_get_args();
        $dbname = $args[0];
        $dbformat = strtoupper($args[1]);

        if (strlen($dbformat) == 0) {
            $dbformat = "GENBANK";
        }
        $this->dbformat = $dbformat;

        $datafile = array();
        for($i = 2; $i < count($args); $i++) {
            $datafile[] = $args[$i];
        }


        /* db exists   fileX args   ACTION   TESTED
            Y            Y        create   okay
            Y            N        use
            N            Y        create    okay
            N            N        create    okay
        */
        // if user provided specific values for $file1, $file2, ... parameters.
        if ((file_exists($dbname)) and (count($datafile) > 0)) {
            // For now, assume USING/OPENING a database is to be done in READ ONLY MODE.
            $this->open($dbname);
        } else {
            $fp = fopen($dbname . ".idx", "w+");
            $fpdir = fopen($dbname . ".dir", "w+");

            // Creates blank data and directory index files, and sets seqptr to 0, etc.
            $this->open($dbname);

            // if user did not provide any datafile name.
            if (count($datafile) == 0) {
                return;
            }

            $temp_r = array();
            // Build our *.DIR file
            foreach($datafile as $fileno=>$filename) {
                $outline = "$fileno $filename\n";
                fputs($fpdir, $outline);

                // Automatically create an index file containing info across all data files.
                $flines = file($filename);
                $totlines = count($flines);

                while(list($lineno, $linestr) = each($flines)) {
                    if (at_entrystart($linestr, $dbformat)) {
                        $current_id =  get_entryid($flines, $linestr, $dbformat);
                        $outline = "$current_id $fileno $lineno\n";
                        // Put entries in an array first, sort them, then write to *.IDX file.
                        $temp_r[$current_id] = array($current_id, $fileno, $lineno);
                    }
                }
                ksort($temp_r);
            }
            // Build our *.IDX array.
            $this->seqcount = count($temp_r);
            foreach($temp_r as $seqid => $line_r) {
                $outline = $line_r[0] . " " . $line_r[1] . " " . $line_r[2] . "\n";
                $fio = fputs($fp, $outline);
            }
        }
        fclose($fp);
        fclose($fpdir);
    }


    public function getDbname()
    {
        return $this->dbname;
    }
    public function setDbname($dbname)
    {
        $this->dbname = $dbname;
    }
    
    public function getDataFn()
    {
        return $this->data_fn;
    }
    public function setDataFn($data_fn)
    {
        $this->data_fn = $data_fn;
    }
    
    public function getDataFp()
    {
        return $this->data_fp;
    }
    public function setDataFp($data_fp)
    {
        $this->data_fp = $data_fp;
    }
    
    public function getDirFn()
    {
        return $this->dir_fn;
    }
    public function setDirFn($dir_fn)
    {
        $this->dir_fn = $dir_fn;
    }
    
    public function getDirFp()
    {
        return $this->dir_fp;
    }
    public function setDirFp($dir_fp)
    {
        $this->dir_fp = $dir_fp;
    }
    
    public function getSeqptr()
    {
        return $this->seqptr;
    }
    public function setSeqptr($seqptr)
    {
        $this->seqptr =$seqptr;
    }
    
    public function getSeqcount()
    {
        return $this->seqcount;
    }
    public function setSeqcount($seqcount)
    {
        $this->seqcount = $seqcount;
    }
    
    public function getDbformat()
    {
        return $this->dbformat;
    }
    public function setDbformat($dbformat)
    {
        $this->dbformat = $dbformat;
    }
    
    public function getBof()
    {
        return $this->bof;
    }
    public function setBof($bof)
    {
        $this->bof = $bof;
    }
    
    public function getEof()
    {
        return $this->eof;
    }
    public function setEof($eof)
    {
        $this->eof = $eof;
    }
}
