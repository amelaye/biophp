<?php
/**
 * Persistence of what the parsers read
 * Freely inspired by BioPHP's project biophp.org
 * Created 20 September 2026
 * Last modified 20 September 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Interfaces;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\ParsedRecord;

/**
 * Interface RecordStorageInterface
 * @package Amelaye\BioPHP\Domain\Database\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface RecordStorageInterface
{
    /**
     * Parses the lines of one record and stores what was read. A record already stored under the
     * same format and identifier is updated rather than duplicated.
     * @param       string              $sDbFormat      Database format, e.g. "GENBANK"
     * @param       array               $aLines         The lines of the record
     * @param       Collection|null     $oCollection    The collection the record belongs to
     * @return      ParsedRecord
     * @throws      \Exception
     */
    public function store(string $sDbFormat, array $aLines, ?Collection $oCollection = null): ParsedRecord;

    /**
     * Parses every record of one or several data files and stores them in a collection.
     * @param       string      $sDbName        Name of the collection, created when it does not exist
     * @param       string      $sDbFormat      Database format, e.g. "GENBANK"
     * @param       string      ...$aDataFiles  Files to parse
     * @return      int                         Number of records stored
     * @throws      \Exception
     */
    public function storeFile(string $sDbName, string $sDbFormat, string ...$aDataFiles): int;

    /**
     * @param       string      $sDbFormat      Database format, e.g. "GENBANK"
     * @param       string      $sEntryId       Identifier of the record
     * @return      ParsedRecord|null
     */
    public function find(string $sDbFormat, string $sEntryId): ?ParsedRecord;
}
