<?php
/**
 * Factory recording different databases format
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Database\Factory;

/**
 * Class DatabaseRecorderFactory
 * @package Amelaye\BioPHP\Domain\Database\Factory
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class DatabaseRecorderFactory
{
    /**
     * Finds the entry start of the file
     * @param   string      $sType          Database format
     * @param   string      $sLinestr       The line to analyze
     * @return  bool
     * @throws  \Exception
     */
    public static function getEntryStart(string $sType, string $sLinestr) : bool {
        $sClass = DatabaseParserFactory::getParserClass($sType);

        return $sClass::isEntryStart($sLinestr);
    }

    /**
     * Finds the entry end of the file
     * @param   string      $sType          Database format
     * @param   string      $sLinestr       The line to analyze
     * @return  bool
     * @throws  \Exception
     */
    public static function getEntryEnd(string $sType, string $sLinestr) : bool {
        $sClass = DatabaseParserFactory::getParserClass($sType);

        return $sClass::isEntryEnd($sLinestr);
    }

    /**
     * Finds the Entry ID
     * @param   string      $sType          Database format
     * @param   array       $flines         Buffed file as array
     * @param   string      $linestr        Current line
     * @return  string
     * @throws  \Exception
     */
    public static function getEntryId(string $sType, array $flines, string $linestr) : string {
        $sClass = DatabaseParserFactory::getParserClass($sType);

        return $sClass::getEntryId($flines, $linestr);
    }
}
