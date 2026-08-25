<?php
/**
 * Factory recording different databases format
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 25 August 2026
 */
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
    public static function getEntryStart($sType, $sLinestr)
    {
        $sClass = DatabaseParserFactory::getParserClass($sType);

        return $sClass::isEntryStart($sLinestr);
    }

    /**
     * Finds the Entry ID
     * @param   string      $sType          Database format
     * @param   array       $flines         Buffed file as array
     * @param   string      $linestr        Current line
     * @return  string
     * @throws  \Exception
     */
    public static function getEntryId($sType, $flines, $linestr)
    {
        $sClass = DatabaseParserFactory::getParserClass($sType);

        return $sClass::getEntryId($flines, $linestr);
    }
}
