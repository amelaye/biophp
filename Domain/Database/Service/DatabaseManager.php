<?php
/**
 * Biological Databases Managing
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 20 September 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Factory\DatabaseParserFactory;
use Amelaye\BioPHP\Domain\Database\Factory\DatabaseReaderFactory;
use Amelaye\BioPHP\Domain\Database\Interfaces\DatabaseInterface;
use Amelaye\BioPHP\Domain\Sequence\Traits\FormatsTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;
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
 * @package Amelaye\BioPHP\Domain\Database\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class DatabaseManager implements DatabaseInterface
{
    use FormatsTrait;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $sPath;

    /**
     * DatabaseManager constructor.
     * @param EntityManagerInterface     $em       Entity Manager, for Doctrine
     * @param string                     $sPath    Path to the data
     */
    public function __construct(EntityManagerInterface $em, string $sPath)
    {
        $this->em    = $em;
        $this->sPath = $sPath;
    }

    /**
     * Retrieves all data from the specified sequence record and returns them in the
     * form of a Seq object.  This method invokes one of several parser methods.
     * @param       string          $sSeqId        The id of the seq obj.
     * @return      ParseSwissprotManager | ParseGenbankManager | ParseEmblManager | bool
     * @throws      \Exception
     */
    public function fetch($sSeqId)
    {
        $collectionDB  = $this->em->getRepository(CollectionElement::class)->findOneBy(['idElement' => $sSeqId]);

        if (empty($collectionDB)) {
            return false;
        }
        if(!is_file($this->sPath . $collectionDB->getFileName())) {
            throw new FileException("The file " . $this->sPath . $collectionDB->getFileName()." doesn't exist !");
        }

        $fpSeq = fopen( $this->sPath . $collectionDB->getFileName(), "r");
        // A data file holds thousands of records one after the other, so reading from its
        // first line would always hand back its first record. recording() noted where each
        // record begins for that very reason.
        $aFlines = $this->line2r($fpSeq, $collectionDB->getDbFormat(), (int) $collectionDB->getLineNo());
        fclose($fpSeq);
        $oService = DatabaseReaderFactory::readDatabase($collectionDB->getDbFormat(), $aFlines);
        return $oService;
    }

    /**
     * Records the new elements of a collection, reads a collection
     *  db exists   fileX args   ACTION
     *     Y            Y        create
     *     Y            N        use
     *     N            Y        create
     *     N            N        create
     * @param       string      $sDbName        Name of the database
     * @param       string      $sDbFormat      Format of the database
     * @param       mixed       ...$sDataFile   Files to parse
     * @throws      \Exception
     */
    public function recording($sDbName, $sDbFormat = "GENBANK", ...$sDataFile)
    {
        $oCollection = new Collection();
        $oCollection->setNomCollection($sDbName);

        $oCollectionExists = $this->em->getRepository(Collection::class)
            ->findOneBy(['nomCollection' => $sDbName]);

        // if user provided specific values for $file1, $file2, ... parameters.
        if ((empty($oCollectionExists)) and (count($sDataFile) > 0)) {
            // For now, assume USING/OPENING a database is to be done in READ ONLY MODE.
            $this->em->persist($oCollection);
            $this->em->flush();
        } else {
            $oCollection = $oCollectionExists;
        }

        // if user did not provide any datafile name.
        if (count($sDataFile) == 0) {
            throw new \Exception("No files provided !");
        }

        $temp_r = array();

        foreach($sDataFile as $fileno => $filename) {
            // Automatically create an index file containing info across all data files.
            $flines = file($this->sPath .$filename);

            foreach(EntryReader::read($flines, $sDbFormat) as $aEntry) {
                // Index row of one entry : what identifies it, the file holding it and the line
                // it starts at, which is what lets fetch() reach it again.
                $temp_r[$aEntry["id"]] = array(
                    "id_element" => $aEntry["id"],
                    "filename"   => $filename,
                    "dbformat"   => $sDbFormat,
                    "line_no"    => $aEntry["line_no"]
                );
            }
        }

        foreach($temp_r as $seqid => $line_r) {
            // Check on the record rather than on the file it comes from : a data file holds
            // thousands of them, and stopping at the first would index only one per file.
            $collectionElementExists = $this->em->getRepository(CollectionElement::class)
                ->findOneBy(['idElement' => $line_r["id_element"]]);

            if(empty($collectionElementExists)) {
                $collectionElement = new CollectionElement();
                $collectionElement->setIdElement($line_r["id_element"]);
                $collectionElement->setCollection($oCollection);
                $collectionElement->setFileName($line_r["filename"]);
                $collectionElement->setSeqCount(count($temp_r));
                $collectionElement->setLineNo($line_r["line_no"]);
                $collectionElement->setDbFormat($line_r["dbformat"]);

                $this->em->persist($collectionElement);
                $this->em->flush();
            }
        }
    }

    /**
     * Copies the lines belonging to a single sequence entry into an array.
     * @param   $fpseq
     * @return  array|bool
     * @throws  \Exception
     */
    private function line2r($fpseq, $sDbFormat, $iLineNo = 0)
    {
        // Which line closes an entry is a property of the format, so the parser is asked
        // rather than guessed at here : "//" is the GenBank family convention, "END" the PDB
        // one, and some formats use neither.
        $sParser = DatabaseParserFactory::getParserClass($sDbFormat);

        // Skip whatever precedes the record asked for, be it the records before it or the
        // header a data file may open with.
        for ($i = 0; $i < $iLineNo; $i++) {
            if (fgets($fpseq) === false) {
                return array();
            }
        }

        $flines = array();
        while(1) {
            // No length limit : a hundred character cap used to cut a longer line in two,
            // handing the parser a second line whose label column held the middle of a word.
            // recording() reads the same files with file(), which has never had that cap.
            $linestr = fgets($fpseq);
            if ($linestr === false) {
                return $flines;
            }
            $flines[] = $linestr;
            if ($sParser::isEntryEnd($linestr)) {
                return $flines;
            }
        }
    }
} 