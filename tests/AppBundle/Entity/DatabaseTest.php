<?php
namespace Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use AppBundle\Entity\Database;

class DatabaseTest extends TestCase
{
    /**
     * Tests for Protein Entity
     */
    public function testNewProtein()
    {
        $oDatabase = new Database("myolddb", "GANBANK", "test.db");

        $oDatabase->setDbname("masuperdb");
        $oDatabase->setDatafile("maseq.seq");
        $oDatabase->setDataFn("myfirstdb.idx");
        $oDatabase->setDataFp("Chemin DATAFP");
        $oDatabase->setDirFn("myfirstdb.dir");
        $oDatabase->setDirFp("Chemin DIRFN");
        $oDatabase->setSeqptr("0");
        $oDatabase->setSeqcount(2);
        $oDatabase->setDbformat("GENBANK");
        $oDatabase->setBof(2);
        $oDatabase->setEof(3);

        $dbname = $oDatabase->getDbname();
        $this->assertEquals("masuperdb", $dbname);

        $datafile = $oDatabase->getDatafile();
        $this->assertEquals("maseq.seq", $datafile);

        $data_fn = $oDatabase->getDataFn();
        $this->assertEquals("myfirstdb.idx", $data_fn);

        $data_fp = $oDatabase->getDataFp();
        $this->assertEquals("Chemin DATAFP", $data_fp);

        $dir_fn = $oDatabase->getDirFn();
        $this->assertEquals("myfirstdb.dir", $dir_fn);

        $dir_fp = $oDatabase->getDirFp();
        $this->assertEquals("Chemin DIRFN", $dir_fp);

        $seqptr = $oDatabase->getSeqptr();
        $this->assertEquals("0", $seqptr);

        $seqcount = $oDatabase->getSeqcount();
        $this->assertEquals(2, $seqcount);

        $dbformat = $oDatabase->getDbformat();
        $this->assertEquals("GENBANK", $dbformat);

        $bof = $oDatabase->getBof();
        $this->assertEquals(2, $bof);

        $eof = $oDatabase->getEof();
        $this->assertEquals(3, $eof);
    }
}