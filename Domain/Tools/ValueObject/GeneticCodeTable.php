<?php
/**
 * Class of constants naming the supported NCBI genetic code translation tables
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\ValueObject;

/**
 * Numbering matches NCBI's own "Genetic Codes" table IDs, so a caller already familiar with that
 * numbering (or reading it off a GenBank /transl_table qualifier) can use it directly. The tables
 * are those of NCBI's gc.prt (version 4.6) whose stop codons are the same in every context, which
 * Biopython's CodonTable agrees with on all 64 codons. Left out are 27 (Karyorelict), 28
 * (Condylostoma) and 31 (Blastocrithidia) : their UGA, and for the last two UAA and UAG, is a stop
 * or an amino acid according to its position, which a table giving one reading per codon cannot say.
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
    const SUPPORTED_TABLES = [1, 2, 3, 4, 5, 6, 9, 10, 11, 12, 13, 14, 15, 16, 21, 22, 23, 24, 25, 26, 29, 30, 32, 33];

    /**
     * @var     string[]    NCBI's name of each supported table
     */
    const NAMES = [
        1 => "Standard",
        2 => "Vertebrate Mitochondrial",
        3 => "Yeast Mitochondrial",
        4 => "Mold Mitochondrial; Protozoan Mitochondrial; Coelenterate
 Mitochondrial; Mycoplasma; Spiroplasma",
        5 => "Invertebrate Mitochondrial",
        6 => "Ciliate Nuclear; Dasycladacean Nuclear; Hexamita Nuclear",
        9 => "Echinoderm Mitochondrial; Flatworm Mitochondrial",
        10 => "Euplotid Nuclear",
        11 => "Bacterial, Archaeal and Plant Plastid",
        12 => "Alternative Yeast Nuclear",
        13 => "Ascidian Mitochondrial",
        14 => "Alternative Flatworm Mitochondrial",
        15 => "Blepharisma Macronuclear",
        16 => "Chlorophycean Mitochondrial",
        21 => "Trematode Mitochondrial",
        22 => "Scenedesmus obliquus Mitochondrial",
        23 => "Thraustochytrium Mitochondrial",
        24 => "Rhabdopleuridae Mitochondrial",
        25 => "Candidate Division SR1 and Gracilibacteria",
        26 => "Pachysolen tannophilus Nuclear",
        29 => "Mesodinium Nuclear",
        30 => "Peritrich Nuclear",
        32 => "Balanophoraceae Plastid",
        33 => "Cephalodiscidae Mitochondrial",
    ];
}
