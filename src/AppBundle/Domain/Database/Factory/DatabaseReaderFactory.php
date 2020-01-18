<?php
/**
 * Factory reading different databases format
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 18 january 2020
 */
namespace AppBundle\Domain\Database\Factory;

use AppBundle\Domain\Database\Service\ParseGenbankManager;
use AppBundle\Domain\Database\Service\ParseSwissprotManager;

/**
 * Class DatabaseReaderFactory
 * @package AppBundle\Domain\Database\Factory
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class DatabaseReaderFactory
{
    /**
     * @param   string      $sType          Database format
     * @param   array       $aFlines        Parsed database
     * @return  ParseGenbankManager|ParseSwissprotManager
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
            default:
                throw new \Exception("Unknown database format ! ");
        }
        return $oService;
    }
}