<?php
namespace Tests\Api;

use PHPUnit\Framework\TestCase;

/**
 * Guards the genetic code, pK, reduced alphabet, amino acid and nucleotide reference data (the
 * samples mirror bioapi's DataFixtures) against the kinds of errors found in them : a codon listed
 * twice, ATA left in Ile for the vertebrate mitochondrial code, a degenerate codon covering the
 * wrong amino acid, a pK of 125, a residue in two classes of a reduced alphabet, the B/Z/X weight
 * ranges swapped, a 3-letter code typo. Expected values come from the NCBI genetic code tables,
 * Solomon, IMGT (Pommié et al. 2004) and Biopython, the reference of every molecular weight.
 */
class BiologicalReferenceDataTest extends TestCase
{
    private const AMINO_ACIDS = "ACDEFGHIKLMNPQRSTVWY";

    /**
     * The amino acid each slot of a species' groups and degenerate codons stands for.
     */
    private const SLOTS = "FLIMVSPTAY*HQNKDECWRG";

    /**
     * NCBI genetic codes, codons in TCAG order (TTT, TTC, TTA, TTG, TCT, ...).
     */
    private const NCBI_TABLES = [
        "standard" => "FFLLSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
        "vertebrate mitochondrial" => "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIMMTTTTNNKKSS**VVVVAAAADDEEGGGG",
        "yeast mitochondrial" => "FFLLSSSSYY**CCWWTTTTPPPPHHQQRRRRIIMMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
        "mold protozoan coelenterate mitochondrial" => "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
        "invertebrate mitochondrial" => "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIMMTTTTNNKKSSSSVVVVAAAADDEEGGGG",
        "ciliate dasycladacean hexamita nuclear" => "FFLLSSSSYYQQCC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
        "echinoderm mitochondrial" => "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIIMTTTTNNNKSSSSVVVVAAAADDEEGGGG",
        "euplotid nuclear" => "FFLLSSSSYY**CCCWLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
        "bacterial plant plastid" => "FFLLSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
        "alternative yeast nuclear" => "FFLLSSSSYY**CC*WLLLSPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
        "ascidian mitochondria" => "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIMMTTTTNNKKSSGGVVVVAAAADDEEGGGG",
        "flatworm mitochondrial" => "FFLLSSSSYYY*CCWWLLLLPPPPHHQQRRRRIIIMTTTTNNNKSSSSVVVVAAAADDEEGGGG",
        "blepharisma macronuclear" => "FFLLSSSSYY*QCC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
        "chlorophycean mitochondrial" => "FFLLSSSSYY*LCC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
        "trematode mitochondrial" => "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIMMTTTTNNNKSSSSVVVVAAAADDEEGGGG",
        "scenedesmus obliquus mitochondrial" => "FFLLSS*SYY*LCC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
        "thraustochytrium mitochondrial code" => "FF*LSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG",
    ];

    private const IUPAC = [
        "A" => "A", "C" => "C", "G" => "G", "T" => "T", "R" => "AG", "Y" => "CT", "S" => "CG",
        "W" => "AT", "K" => "GT", "M" => "AC", "B" => "CGT", "D" => "AGT", "H" => "ACT",
        "V" => "ACG", "N" => "ACGT",
    ];

    /**
     * @return string[]     The 64 codons in TCAG order
     */
    private static function codons(): array
    {
        $aCodons = [];
        foreach (str_split("TCAG") as $s1) {
            foreach (str_split("TCAG") as $s2) {
                foreach (str_split("TCAG") as $s3) {
                    $aCodons[] = $s1 . $s2 . $s3;
                }
            }
        }
        return $aCodons;
    }

    private static function species(): array
    {
        require __DIR__ . '/samples/TripletsSpecies.php';
        $aByNature = [];
        foreach ($aTripletSpeciesObjects as $oSpecies) {
            $aByNature[$oSpecies->getNature()] = $oSpecies;
        }
        return $aByNature;
    }

    private static function aminos(): array
    {
        require __DIR__ . '/samples/Aminos.php';
        $aById = [];
        foreach ($aAminosObjects as $oAmino) {
            $aById[$oAmino->getId()] = $oAmino;
        }
        return $aById;
    }

    public function testTheTripletListHoldsEachOfThe64CodonsOnce()
    {
        require __DIR__ . '/samples/Triplets.php';
        $aTriplets = array_map(fn($oTriplet) => $oTriplet->getTriplet(), $aTripletObjects);
        sort($aTriplets);
        $aExpected = self::codons();
        sort($aExpected);

        $this->assertSame($aExpected, $aTriplets);
    }

    public function testEverySpeciesIsListed()
    {
        $this->assertEqualsCanonicalizing(array_keys(self::NCBI_TABLES), array_keys(self::species()));
    }

    public function testEveryCodonFallsInTheGroupOfItsNcbiAminoAcid()
    {
        foreach (self::species() as $sNature => $oSpecies) {
            $aTruth = array_combine(self::codons(), str_split(self::NCBI_TABLES[$sNature]));
            $aGroups = $oSpecies->getTripletsGroups();
            foreach ($aTruth as $sCodon => $sAmino) {
                $aFound = [];
                foreach (str_split(self::SLOTS) as $i => $sSlot) {
                    if (preg_match('/^' . $aGroups[$i] . '$/', $sCodon . " ")) {
                        $aFound[] = $sSlot;
                    }
                }
                $this->assertSame([$sAmino], $aFound, "$sNature : $sCodon");
            }
        }
    }

    /**
     * Each degenerate codon is the tightest IUPAC triplet covering all the codons of its amino
     * acid : for each position, the code of the bases found there. A six-codon amino acid (Leu,
     * Ser, Arg) cannot be written as one exact triplet, which is why the tightest one is the rule.
     */
    public function testEveryDegenerateCodonIsTheTightestTripletCoveringItsAminoAcid()
    {
        $aCodeOf = array_flip(self::IUPAC);
        foreach (self::species() as $sNature => $oSpecies) {
            $aTruth = array_combine(self::codons(), str_split(self::NCBI_TABLES[$sNature]));
            $aDegenerate = $oSpecies->getTriplets();
            foreach (str_split(self::SLOTS) as $i => $sSlot) {
                $aCodons = array_keys($aTruth, $sSlot, true);
                $sTightest = "";
                for ($iPos = 0; $iPos < 3; $iPos++) {
                    $aBases = array_unique(array_map(fn($sCodon) => $sCodon[$iPos], $aCodons));
                    usort($aBases, fn($a, $b) => strpos("ACGT", $a) <=> strpos("ACGT", $b));
                    $sTightest .= $aCodeOf[implode("", $aBases)];
                }
                $this->assertSame($sTightest, $aDegenerate[$i], "$sNature : $sSlot");
            }
            $this->assertSame("NNN", $aDegenerate[21], "$sNature : X");
        }
    }

    public function testPkValuesMatchTheirSource()
    {
        require __DIR__ . '/samples/PK.php';
        $aExpected = [
            // N-term, K, R, H, C-term, D, E, C, Y
            "EMBOSS" => [8.6, 10.8, 12.5, 6.5, 3.6, 3.9, 4.1, 8.5, 10.1],
            "DTASelect" => [8.0, 10.0, 12.0, 6.5, 3.1, 4.4, 4.4, 8.5, 10.0],
            "Solomon" => [9.6, 10.5, 12.5, 6.0, 2.4, 3.9, 4.3, 8.3, 10.1],
        ];
        $this->assertCount(count($aExpected), $aPKObjects);
        foreach ($aPKObjects as $oPk) {
            $aValues = [$oPk->getNTerminus(), $oPk->getK(), $oPk->getR(), $oPk->getH(), $oPk->getCTerminus(),
                $oPk->getD(), $oPk->getE(), $oPk->getC(), $oPk->getY()];
            $this->assertEquals($aExpected[$oPk->getId()], $aValues, $oPk->getId());
        }
    }

    public function testEveryReducedAlphabetPutsEachAminoAcidInExactlyOneClass()
    {
        require __DIR__ . '/samples/ProteinReductions.php';
        $aMembers = [];
        foreach ($aReductions as $oReduction) {
            if ($oReduction->getPattern() !== "-") {
                $aMembers[$oReduction->getAlphabet()][] = $oReduction->getPattern();
            }
        }
        foreach ($aMembers as $sAlphabet => $aPatterns) {
            $aLetters = explode("|", implode("|", $aPatterns));
            sort($aLetters);
            $this->assertSame(str_split(self::AMINO_ACIDS), $aLetters, "Alphabet $sAlphabet");
        }
    }

    public function testImgtHydropathyClasses()
    {
        require __DIR__ . '/samples/ProteinReductions.php';
        $aClasses = [];
        foreach ($aReductions as $oReduction) {
            if ($oReduction->getAlphabet() === "3IMG") {
                $aLetters = explode("|", $oReduction->getPattern());
                sort($aLetters);
                $aClasses[$oReduction->getReduction()] = implode("", $aLetters);
            }
        }
        // Pommié et al. (2004) : hydrophobic A C I L M F W V, neutral G H P S T Y, hydrophilic R N D Q E K
        $this->assertSame(["p" => "DEKNQR", "n" => "GHPSTY", "h" => "ACFILMVW"], $aClasses);
    }

    public function testThreeLetterCodesAreTheIupacOnes()
    {
        $aExpected = [
            "A" => "Ala", "C" => "Cys", "D" => "Asp", "E" => "Glu", "F" => "Phe", "G" => "Gly", "H" => "His",
            "I" => "Ile", "K" => "Lys", "L" => "Leu", "M" => "Met", "N" => "Asn", "O" => "Pyl", "P" => "Pro",
            "Q" => "Gln", "R" => "Arg", "S" => "Ser", "T" => "Thr", "U" => "Sec", "V" => "Val", "W" => "Trp",
            "Y" => "Tyr",
        ];
        $aAminos = self::aminos();
        foreach ($aExpected as $sId => $sCode) {
            $this->assertSame($sCode, $aAminos[$sId]->getName3Letters(), $sId);
        }
    }

    /**
     * An ambiguity code weighs between the lightest and the heaviest amino acid it stands for :
     * B is N or D, Z is Q or E, X is any of the twenty.
     */
    public function testAmbiguityCodesSpanTheWeightsOfTheAminoAcidsTheyStandFor()
    {
        $aAminos = self::aminos();
        foreach (["B" => "ND", "Z" => "QE", "X" => self::AMINO_ACIDS] as $sId => $sMembers) {
            $aWeights = array_map(fn($s) => $aAminos[$s]->getWeight1(), str_split($sMembers));
            $this->assertEquals(min($aWeights), $aAminos[$sId]->getWeight1(), "$sId lower");
            $this->assertEquals(max($aWeights), $aAminos[$sId]->getWeight2(), "$sId upper");
        }
    }

    /**
     * Free amino acid weights are Biopython's Bio.Data.IUPACData.protein_weights, which
     * Bio.SeqUtils.molecular_weight uses : ProteinManager::molwt() then equals it.
     */
    public function testAminoAcidWeightsAreBiopythonOnes()
    {
        $aBiopython = [
            "A" => 89.0932, "C" => 121.1582, "D" => 133.1027, "E" => 147.1293, "F" => 165.1891, "G" => 75.0666,
            "H" => 155.1546, "I" => 131.1729, "K" => 146.1876, "L" => 131.1729, "M" => 149.2113, "N" => 132.1179,
            "O" => 255.3134, "P" => 115.1305, "Q" => 146.1445, "R" => 174.201, "S" => 105.0926, "T" => 119.1192,
            "U" => 168.0532, "V" => 117.1463, "W" => 204.2252, "Y" => 181.1885,
        ];
        $aAminos = self::aminos();
        foreach ($aBiopython as $sId => $fWeight) {
            $this->assertEquals($fWeight, $aAminos[$sId]->getWeight1(), $sId);
            $this->assertEquals($fWeight, $aAminos[$sId]->getWeight2(), $sId);
        }
    }

    /**
     * A residue is its free amino acid less one water (18.0153, Biopython's average water).
     */
    public function testResidueWeightsAreFreeWeightsLessOneWater()
    {
        foreach (self::aminos() as $sId => $oAmino) {
            if (strpos(self::AMINO_ACIDS . "OU", $sId) !== false) {
                $this->assertEqualsWithDelta($oAmino->getWeight1() - 18.0153, $oAmino->getResidueMolWeight(), 0.00005, $sId);
            }
        }
    }

    /**
     * Nucleotide residue weights are Biopython's nucleoside monophosphates
     * (Bio.Data.IUPACData.unambiguous_dna_weights / unambiguous_rna_weights) less one water
     * (18.0153) : SequenceManager::molwt() then equals Bio.SeqUtils.molecular_weight.
     */
    public function testNucleotideResidueWeightsAreBiopythonMonophosphatesLessOneWater()
    {
        require __DIR__ . '/samples/Nucleotids.php';
        $aWeights = [];
        foreach ($aNucleoObjects as $oNucleotide) {
            $aWeights[$oNucleotide->getNature()][$oNucleotide->getLetter()] = $oNucleotide->getWeight();
        }
        $aMonophosphates = [
            "DNA" => ["A" => 331.2218, "T" => 322.2085, "G" => 347.2212, "C" => 307.1971],
            "RNA" => ["A" => 347.2212, "U" => 324.1813, "G" => 363.2206, "C" => 323.1965],
        ];
        foreach ($aMonophosphates as $sNature => $aBases) {
            foreach ($aBases as $sBase => $fWeight) {
                $this->assertEqualsWithDelta($fWeight - 18.0153, $aWeights[$sNature][$sBase], 0.00005, "$sNature $sBase");
            }
        }

        require __DIR__ . '/samples/Elements.php';
        $this->assertEquals(18.0153, $aElementsObjects[5]->getWeight(), "water");
    }
}
