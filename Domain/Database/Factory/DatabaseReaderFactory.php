<?php
/**
 * Factory reading different databases format
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 19 january 2020
 */
namespace Amelaye\BioPHP\Domain\Database\Factory;

use Amelaye\BioPHP\Domain\Database\Service\ParseEmblManager;
use Amelaye\BioPHP\Domain\Database\Service\ParseExpasyEnzymeManager;
use Amelaye\BioPHP\Domain\Database\Service\ParseGenbankManager;
use Amelaye\BioPHP\Domain\Database\Service\ParsePdbManager;
use Amelaye\BioPHP\Domain\Database\Service\ParsePrositeManager;
use Amelaye\BioPHP\Domain\Database\Service\ParseSwissprotManager;

/**
 * Class DatabaseReaderFactory
 * @package Amelaye\BioPHP\Domain\Database\Factory
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class DatabaseReaderFactory
{
    /**
     * @param   string      $sType          Database format
     * @param   array       $aFlines        Parsed database
     * @return  ParseGenbankManager|ParseSwissprotManager|ParseEmblManager|ParsePdbManager|ParsePrositeManager|ParseExpasyEnzymeManager
     * @throws  \Exception
     */
    public static function readDatabase($sType, $aFlines)
    {
        switch($sType) {
            case "GENBANK":
                $oService = new ParseGenbankManager();
                $oService->parseDataFile($aFlines);
                break;
            case "SWISSPROT":
                $oService = new ParseSwissprotManager();
                $oService->parseDataFile($aFlines);
                break;
            case "EMBL":
                $oService = new ParseEmblManager();
                $oService->parseDataFile($aFlines);
                break;
            case "PDB":
                $oService = new ParsePdbManager();
                $oService->parseDataFile($aFlines);
                break;
            case "PROSITE":
                $oService = new ParsePrositeManager();
                $oService->parseDataFile($aFlines);
                break;
            case "EXPASY_ENZYME":
                $oService = new ParseExpasyEnzymeManager();
                $oService->parseDataFile($aFlines);
                break;
            default:
                throw new \Exception("Unknown database format ! ");
        }
        return $oService;
    }
}