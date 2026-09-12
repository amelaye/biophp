<?php
/**
 * Resolves a database format name to the parser that knows it
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Factory;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;
use Amelaye\BioPHP\Domain\Parser\ParseAaindexManager;
use Amelaye\BioPHP\Domain\Parser\ParseBlocksManager;
use Amelaye\BioPHP\Domain\Parser\ParseEmblManager;
use Amelaye\BioPHP\Domain\Parser\ParseEntrezManager;
use Amelaye\BioPHP\Domain\Parser\ParseEpdManager;
use Amelaye\BioPHP\Domain\Parser\ParseExpasyEnzymeManager;
use Amelaye\BioPHP\Domain\Parser\ParseGenbankManager;
use Amelaye\BioPHP\Domain\Parser\ParsePdbManager;
use Amelaye\BioPHP\Domain\Parser\ParseGenomeManager;
use Amelaye\BioPHP\Domain\Parser\ParseHgbaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseKeggCompoundManager;
use Amelaye\BioPHP\Domain\Parser\ParseKeggEnzymeManager;
use Amelaye\BioPHP\Domain\Parser\ParseKeggGenomeManager;
use Amelaye\BioPHP\Domain\Parser\ParseKeggOrthologManager;
use Amelaye\BioPHP\Domain\Parser\ParseKeggReactionManager;
use Amelaye\BioPHP\Domain\Parser\ParseNcbiLitManager;
use Amelaye\BioPHP\Domain\Parser\ParsePdbstrManager;
use Amelaye\BioPHP\Domain\Parser\ParsePirManager;
use Amelaye\BioPHP\Domain\Parser\ParsePmdManager;
use Amelaye\BioPHP\Domain\Parser\ParsePrfManager;
use Amelaye\BioPHP\Domain\Parser\ParsePrintsManager;
use Amelaye\BioPHP\Domain\Parser\ParseProdomManager;
use Amelaye\BioPHP\Domain\Parser\ParsePrositeManager;
use Amelaye\BioPHP\Domain\Parser\ParseSwissprotManager;
use Amelaye\BioPHP\Domain\Parser\ParseTransfacCellManager;
use Amelaye\BioPHP\Domain\Parser\ParseTransfacClassManager;
use Amelaye\BioPHP\Domain\Parser\ParseTransfacFactorManager;
use Amelaye\BioPHP\Domain\Parser\ParseTransfacGeneManager;
use Amelaye\BioPHP\Domain\Parser\ParseTransfacMatrixManager;
use Amelaye\BioPHP\Domain\Parser\ParseTransfacSiteManager;
use Amelaye\BioPHP\Domain\Parser\ParseUnigeneManager;

/**
 * The single place that knows which parsers exist. Everything a format implies - its name, how
 * an entry starts, how its identifier reads, how it is parsed - lives in the parser itself, so
 * supporting a new format means writing one class and adding one line below.
 * Class DatabaseParserFactory
 * @package Amelaye\BioPHP\Domain\Database\Factory
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class DatabaseParserFactory
{
    /**
     * Every parser the library ships with.
     */
    private const PARSERS = [
        ParseGenbankManager::class,
        ParseSwissprotManager::class,
        ParseEmblManager::class,
        ParsePdbManager::class,
        ParsePrositeManager::class,
        ParseExpasyEnzymeManager::class,
        ParsePdbstrManager::class,
        ParseUnigeneManager::class,
        ParsePrintsManager::class,
        ParseBlocksManager::class,
        ParseAaindexManager::class,
        ParseProdomManager::class,
        ParseNcbiLitManager::class,
        ParsePmdManager::class,
        ParseHgbaseManager::class,
        ParsePrfManager::class,
        ParsePirManager::class,
        ParseEpdManager::class,
        ParseGenomeManager::class,
        ParseEntrezManager::class,
        ParseTransfacMatrixManager::class,
        ParseTransfacGeneManager::class,
        ParseTransfacClassManager::class,
        ParseTransfacCellManager::class,
        ParseTransfacFactorManager::class,
        ParseTransfacSiteManager::class,
        ParseKeggCompoundManager::class,
        ParseKeggReactionManager::class,
        ParseKeggEnzymeManager::class,
        ParseKeggOrthologManager::class,
        ParseKeggGenomeManager::class
    ];

    /**
     * The format names accepted by the library, in registration order.
     * @return array
     */
    public static function getFormats() : array
    {
        $aFormats = [];
        foreach(self::PARSERS as $sClass) {
            $aFormats[] = $sClass::getFormat();
        }

        return $aFormats;
    }

    /**
     * Resolves a format name to the class handling it.
     * @param   string      $sFormat        Database format, e.g. "GENBANK"
     * @return  string                      Fully qualified name of the parser class
     * @throws  \Exception                  When no parser claims that format
     */
    public static function getParserClass(string $sFormat) : string
    {
        foreach(self::PARSERS as $sClass) {
            if ($sClass::getFormat() === $sFormat) {
                return $sClass;
            }
        }

        throw new \Exception(
            "Unknown database format ! \"" . $sFormat . "\" is not one of "
            . implode(", ", self::getFormats()) . "."
        );
    }

    /**
     * Builds a fresh parser for a format. A parser holds the state of the record it has read, so
     * each call returns its own instance.
     * @param   string      $sFormat        Database format, e.g. "GENBANK"
     * @return  ParseDatabaseInterface
     * @throws  \Exception                  When no parser claims that format
     */
    public static function createParser(string $sFormat) : ParseDatabaseInterface
    {
        $sClass = self::getParserClass($sFormat);

        return new $sClass();
    }
}
