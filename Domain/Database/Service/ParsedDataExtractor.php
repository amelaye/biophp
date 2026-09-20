<?php
/**
 * Turns what a parser has read into plain data
 * Freely inspired by BioPHP's project biophp.org
 * Created 20 September 2026
 * Last modified 20 September 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Service;

/**
 * Class ParsedDataExtractor
 * The parsers share no common set of fields, but each exposes what it read through public getters.
 * Reading those getters is what lets every parser, including one added later, be stored without
 * any code of its own : getBaseRange() becomes "baseRange", isTruePositive() becomes "truePositive",
 * and an object found on the way (a reference, an atom...) is read the same way, recursively.
 * @package Amelaye\BioPHP\Domain\Database\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class ParsedDataExtractor
{
    private const MAX_DEPTH = 10;

    /**
     * @param   object      $oSource        A parser, once it has read a record
     * @return  array                       The fields it exposes, ready to be encoded as JSON
     */
    public static function extract(object $oSource): array
    {
        return self::readGetters($oSource, [spl_object_id($oSource) => true], 1);
    }

    /**
     * @param   object      $oSource
     * @param   array       $aPath          Objects being read, to stop on a reference to itself
     * @param   int         $iDepth
     * @return  array
     */
    private static function readGetters(object $oSource, array $aPath, int $iDepth): array
    {
        $aResult = [];
        foreach ((new \ReflectionObject($oSource))->getMethods(\ReflectionMethod::IS_PUBLIC) as $oMethod) {
            if ($oMethod->isStatic() || $oMethod->getNumberOfRequiredParameters() > 0) {
                continue;
            }
            if (!preg_match('/^(?:get|is)([A-Z].*)$/', $oMethod->getName(), $aMatches)) {
                continue;
            }
            $aResult[lcfirst($aMatches[1])] = self::normalize($oMethod->invoke($oSource), $aPath, $iDepth);
        }

        return $aResult;
    }

    /**
     * @param   mixed       $mValue
     * @param   array       $aPath
     * @param   int         $iDepth
     * @return  mixed
     */
    private static function normalize($mValue, array $aPath, int $iDepth)
    {
        if ($mValue === null || is_bool($mValue) || is_int($mValue)) {
            return $mValue;
        }
        if (is_float($mValue)) {
            return is_finite($mValue) ? $mValue : null;
        }
        if (is_string($mValue)) {
            return mb_scrub($mValue, "UTF-8");
        }
        if ($iDepth > self::MAX_DEPTH) {
            return null;
        }
        if (is_array($mValue) || $mValue instanceof \Traversable) {
            $aResult = [];
            foreach ($mValue as $mKey => $mItem) {
                $aResult[$mKey] = self::normalize($mItem, $aPath, $iDepth + 1);
            }
            return $aResult;
        }
        if (!is_object($mValue)) {
            return null;
        }
        if ($mValue instanceof \DateTimeInterface) {
            return $mValue->format(\DATE_ATOM);
        }
        if ($mValue instanceof \BackedEnum) {
            return $mValue->value;
        }
        if ($mValue instanceof \UnitEnum) {
            return $mValue->name;
        }
        if (isset($aPath[spl_object_id($mValue)])) {
            return null;
        }

        $aPath[spl_object_id($mValue)] = true;
        $aFields = self::readGetters($mValue, $aPath, $iDepth + 1);
        if (empty($aFields) && $mValue instanceof \Stringable) {
            return (string) $mValue;
        }

        return $aFields;
    }
}
