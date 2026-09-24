<?php
/**
 * Class of constants enumerating the kinds of annotation a PlasmidFeature may carry
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 24 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

/**
 * Class FeatureType - the minimal set of annotation kinds a cloning plasmid needs. The model never
 * infers one of these from a feature's name; the caller (or, later, GenbankPlasmidMapper) decides.
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class FeatureType
{
    const INSERT = "INSERT";
    const CDS = "CDS";
    const PROMOTER = "PROMOTER";
    const TERMINATOR = "TERMINATOR";
    const MARKER = "MARKER";
    const REPORTER = "REPORTER";
    const ORIGIN_OF_REPLICATION = "ORIGIN_OF_REPLICATION";
    const TAG = "TAG";
    const RESTRICTION_SITE = "RESTRICTION_SITE";
    const MISC_FEATURE = "MISC_FEATURE";

    /**
     * @var     string[]
     */
    const VALID_TYPES = [
        self::INSERT,
        self::CDS,
        self::PROMOTER,
        self::TERMINATOR,
        self::MARKER,
        self::REPORTER,
        self::ORIGIN_OF_REPLICATION,
        self::TAG,
        self::RESTRICTION_SITE,
        self::MISC_FEATURE,
    ];
}
