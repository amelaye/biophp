<?php
/**
 * Database Entity
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
namespace AppBundle\Entity;

class Database {

    /**
     * @var string
     */
    private $dbname;
    
    private $datafile;

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
    function __construct($dbname, $dbformat, $datafile)
    {
        $this->dbformat = $dbformat;
        $this->datafile = $datafile;
        $this->dbname   = $dbname;
    }


    public function getDbname()
    {
        return $this->dbname;
    }
    public function setDbname($dbname)
    {
        $this->dbname = $dbname;
    }
    
    public function getDatafile()
    {
        return $this->datafile;
    }
    public function setDatafile($datafile)
    {
        $this->datafile = $datafile;
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
