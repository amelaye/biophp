<?php
/**
 * Factory reading different databases format
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Factory;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class DatabaseReaderFactory
 * @package Amelaye\BioPHP\Domain\Database\Factory
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class DatabaseReaderFactory
{
    /**
     * Builds the parser matching a format and runs it over the buffered file.
     * @param   string      $sType          Database format
     * @param   array       $aFlines        Parsed database
     * @return  ParseDatabaseInterface
     * @throws  \Exception
     */
    public static function readDatabase($sType, $aFlines)
    {
        $oService = DatabaseParserFactory::createParser($sType);
        $oService->parseDataFile($aFlines);

        return $oService;
    }
}
