<?php
/**
 * Factory recording different databases format
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 19 january 2020
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
     * @param $sType
     * @param $sLinestr
     * @return bool
     * @throws  \Exception
     */
    public static function getEntryStart($sType, $sLinestr)
    {
        switch($sType) {
            case "GENBANK":
                return (substr($sLinestr,0,5) == "LOCUS");
                break;
            case "SWISSPROT":
                return (substr($sLinestr,0,2) == "ID");
                break;
            default:
                throw new \Exception("Unknown database format ! ");
        }
    }

    /**
     * Finds the Entry ID
     * @param $sType
     * @param $flines
     * @param $linestr
     * @return array|string
     * @throws  \Exception
     */
    public static function getEntryId($sType, $flines, $linestr)
    {
        switch($sType) {
            case "GENBANK":
                $locus = preg_split("/\s+/", trim($linestr));
                $entyId = $locus[1];
                return trim($entyId);
                break;
            case "SWISSPROT":
                foreach ($flines as $lineno => $linestr) {
                    if (substr($linestr,0,2) == "AC") {
                        $linestr = str_replace(' ', '', substr($linestr,5));
                        $words = preg_split("/;/", $linestr);
                        prev($flines);
                        return $words[0];
                    }
                }
                break;
            default:
                throw new \Exception("Unknown database format ! ");
        }
    }
}