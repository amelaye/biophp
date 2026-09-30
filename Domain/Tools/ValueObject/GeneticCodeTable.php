<?php
/**
 * Class of constants naming the supported NCBI genetic code translation tables
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\ValueObject;

/**
 * Numbering matches NCBI's own "Genetic Codes" table IDs, so a caller already familiar with that
 * numbering (or reading it off a GenBank /transl_table qualifier) can use it directly.
 * Class GeneticCodeTable
 * @package Amelaye\BioPHP\Domain\Tools\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class GeneticCodeTable
{
    const STANDARD = 1;
    const VERTEBRATE_MITOCHONDRIAL = 2;

    /**
     * @var     int[]
     */
    const SUPPORTED_TABLES = [
        self::STANDARD,
        self::VERTEBRATE_MITOCHONDRIAL,
    ];
}
