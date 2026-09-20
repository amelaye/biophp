<?php
/**
 * Persistence of what the parsers read
 * Freely inspired by BioPHP's project biophp.org
 * Created 20 September 2026
 * Last modified 20 September 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\ParsedRecord;
use Amelaye\BioPHP\Domain\Database\Factory\DatabaseParserFactory;
use Amelaye\BioPHP\Domain\Database\Factory\DatabaseReaderFactory;
use Amelaye\BioPHP\Domain\Database\Factory\DatabaseRecorderFactory;
use Amelaye\BioPHP\Domain\Database\Interfaces\RecordStorageInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RecordManager
 * Stores what any of the registered parsers read, whatever its format, as ParsedRecord documents.
 * @package Amelaye\BioPHP\Domain\Database\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class RecordManager implements RecordStorageInterface
{
    /**
     * Records written between two flushes while a file is stored.
     */
    private const BATCH_SIZE = 100;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $sPath;

    /**
     * RecordManager constructor.
     * @param EntityManagerInterface     $em       Entity Manager, for Doctrine
     * @param string                     $sPath    Path to the data
     */
    public function __construct(EntityManagerInterface $em, string $sPath)
    {
        $this->em    = $em;
        $this->sPath = $sPath;
    }

    /**
     * {@inheritdoc}
     */
    public function store(string $sDbFormat, array $aLines, ?Collection $oCollection = null): ParsedRecord
    {
        DatabaseParserFactory::getParserClass($sDbFormat);

        $aPending = [];
        $oRecord = $this->upsert(
            $aPending,
            $sDbFormat,
            DatabaseRecorderFactory::getEntryId($sDbFormat, $aLines, $aLines[0] ?? ""),
            $aLines,
            $oCollection
        );
        $this->em->flush();

        return $oRecord;
    }

    /**
     * {@inheritdoc}
     */
    public function storeFile(string $sDbName, string $sDbFormat, string ...$aDataFiles): int
    {
        DatabaseParserFactory::getParserClass($sDbFormat);
        if (count($aDataFiles) == 0) {
            throw new \InvalidArgumentException("No files provided !");
        }

        $oCollection = $this->em->getRepository(Collection::class)->findOneBy(['nomCollection' => $sDbName]);
        if (empty($oCollection)) {
            $oCollection = new Collection();
            $oCollection->setNomCollection($sDbName);
            $this->em->persist($oCollection);
        }

        $aPending = [];
        $iCount   = 0;
        foreach ($aDataFiles as $sFileName) {
            $aFileLines = is_file($this->sPath . $sFileName) ? file($this->sPath . $sFileName) : false;
            if ($aFileLines === false) {
                throw new \RuntimeException("The file " . $this->sPath . $sFileName . " doesn't exist !");
            }

            foreach (EntryReader::read($aFileLines, $sDbFormat) as $aEntry) {
                $this->upsert($aPending, $sDbFormat, $aEntry["id"], $aEntry["lines"], $oCollection);
                $iCount++;
                if ($iCount % self::BATCH_SIZE == 0) {
                    $this->em->flush();
                }
            }
        }
        $this->em->flush();

        return $iCount;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $sDbFormat, string $sEntryId): ?ParsedRecord
    {
        return $this->em->getRepository(ParsedRecord::class)
            ->findOneBy(['dbFormat' => $sDbFormat, 'entryId' => $sEntryId]);
    }

    /**
     * Parses a record and writes it, or rewrites the one already stored under the same identifier.
     * @param   array               $aPending       Records persisted but not flushed yet, by format and identifier
     * @param   string              $sDbFormat
     * @param   string              $sEntryId
     * @param   array               $aLines
     * @param   Collection|null     $oCollection
     * @return  ParsedRecord
     * @throws  \Exception
     */
    private function upsert(array &$aPending, string $sDbFormat, string $sEntryId, array $aLines, ?Collection $oCollection): ParsedRecord
    {
        $sKey    = $sDbFormat . "|" . $sEntryId;
        $oRecord = $aPending[$sKey] ?? $this->find($sDbFormat, $sEntryId);
        if ($oRecord === null) {
            $oRecord = new ParsedRecord();
            $oRecord->setDbFormat($sDbFormat);
            $oRecord->setEntryId($sEntryId);
            $this->em->persist($oRecord);
        }

        $oRecord->setData(ParsedDataExtractor::extract(DatabaseReaderFactory::readDatabase($sDbFormat, $aLines)));
        $oRecord->setCollection($oCollection);
        $aPending[$sKey] = $oRecord;

        return $oRecord;
    }
}
