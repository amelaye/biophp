<?php
namespace Tests\Api;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Guards the restriction enzyme and PAM250 reference data (the samples mirror bioapi's
 * DataFixtures) against the kinds of errors found in them : numeric cut fields contradicting
 * the annotated site, search patterns missing a site orientation or an IUPAC expansion,
 * neoschizomers grouped as isoschizomers, and a wrong PAM250 cell. Expected values come from
 * REBASE (emboss files v404, via Biopython's Restriction_Dictionary) and Dayhoff (1978).
 *
 * Data conventions : in an annotated site, "'" marks the top-strand cut and "_" the bottom-strand
 * one ; CleavagePosUpper is the top cut position from the start of the pattern, CleavagePosLower
 * the overhang length (positive 5', negative 3', 0 blunt).
 */
class ReferenceDataConsistencyTest extends TestCase
{
    private const IUPAC = [
        "A" => "A", "C" => "C", "G" => "G", "T" => "T", "R" => "AG", "Y" => "CT", "S" => "CG",
        "W" => "AT", "K" => "GT", "M" => "AC", "B" => "CGT", "D" => "AGT", "H" => "ACT",
        "V" => "ACG", "N" => ".",
    ];

    private static function typeII(): array
    {
        require __DIR__ . '/samples/TypeIIEndonucleases.php';
        $aById = [];
        foreach ($aTypeIIEndonucleases as $oEnzyme) {
            $aById[$oEnzyme->getId()] = $oEnzyme;
        }
        return $aById;
    }

    private static function typeIIs(): array
    {
        require __DIR__ . '/samples/Type2sEndonucleases.php';
        $aById = [];
        foreach ($aTypeIIsEndonucleases as $oEnzyme) {
            $aById[$oEnzyme->getId()] = $oEnzyme;
        }
        return $aById;
    }

    private static function typeIIb(): array
    {
        require __DIR__ . '/samples/Type2bEndonucleases.php';
        $aById = [];
        foreach ($aTypeIIbEndonucleases as $oEnzyme) {
            $aById[$oEnzyme->getId()] = $oEnzyme;
        }
        return $aById;
    }

    /**
     * Every concrete sequence an IUPAC site stands for, N kept as the regex wildcard ".".
     * @return string[]
     */
    private static function expand(string $sSite): array
    {
        $aResults = [""];
        foreach (str_split($sSite) as $sCode) {
            $aNext = [];
            foreach ($aResults as $sPrefix) {
                foreach (str_split(self::IUPAC[$sCode]) as $sBase) {
                    $aNext[] = $sPrefix . $sBase;
                }
            }
            $aResults = $aNext;
        }
        sort($aResults);
        return array_values(array_unique($aResults));
    }

    private static function reverseComplement(string $sSite): string
    {
        return strrev(strtr($sSite, "ACGTRYSWKMBDHVN.", "TGCAYRSWMKVHDBN."));
    }

    /**
     * @return string[]     The sorted, unique alternatives of a "(A|B|C)" search pattern
     */
    private static function alternatives(string $sComputingPattern): array
    {
        $aAlternatives = array_values(array_unique(explode("|", trim($sComputingPattern, "()"))));
        sort($aAlternatives);
        return $aAlternatives;
    }

    /**
     * @return int[]        [top cut, bottom cut, site length] read from an annotated site
     */
    private static function cutsOf(string $sAnnotated): array
    {
        $iTop = strpos(str_replace("_", "", $sAnnotated), "'");
        $iBottom = strpos(str_replace("'", "", $sAnnotated), "_");
        return [$iTop, $iBottom === false ? $iTop : $iBottom, strlen(str_replace(["'", "_"], "", $sAnnotated))];
    }

    private static function plainSite(string $sAnnotated): string
    {
        return str_replace(["'", "_"], "", trim($sAnnotated));
    }

    public function testEveryTypeIIEntryAgreesWithItsOwnAnnotatedSite()
    {
        foreach (self::typeII() as $sId => $oEnzyme) {
            foreach (explode(" or ", $oEnzyme->getRecognitionPattern()) as $sAnnotated) {
                [$iTop, $iBottom, $iLength] = self::cutsOf(trim($sAnnotated));
                $this->assertSame($iTop, $oEnzyme->getCleavagePosUpper(), "$sId top cut");
                $this->assertSame($iBottom - $iTop, $oEnzyme->getCleavagePosLower(), "$sId overhang");
                $this->assertSame($iLength, $oEnzyme->getLengthRecognitionPattern(), "$sId length");
            }
        }
    }

    /**
     * A non-palindromic site must also be searched as its reverse complement, or a site on the
     * bottom strand is missed (BauI lacked CTCGTG).
     */
    public function testEveryTypeIISearchPatternCoversBothOrientationsAndEveryIupacExpansion()
    {
        foreach (self::typeII() as $sId => $oEnzyme) {
            $sSite = self::plainSite(explode(" or ", $oEnzyme->getRecognitionPattern())[0]);
            $aExpected = array_values(array_unique(array_merge(
                self::expand($sSite),
                self::expand(self::reverseComplement($sSite))
            )));
            sort($aExpected);
            $this->assertSame($aExpected, self::alternatives($oEnzyme->getComputingPattern()), $sId);
        }
    }

    public function testEveryTypeIIsEntryAgreesWithItsSiteAndHasAMatchingReverseRow()
    {
        $aEnzymes = self::typeIIs();
        foreach ($aEnzymes as $sId => $oEnzyme) {
            if (str_ends_with($sId, "@")) {
                continue;
            }
            $aForward = [];
            foreach (explode(" or ", $oEnzyme->getRecognitionPattern()) as $sAnnotated) {
                [$iTop, $iBottom, $iLength] = self::cutsOf(trim($sAnnotated));
                $this->assertSame($iTop, $oEnzyme->getCleavagePosUpper(), "$sId top cut");
                $this->assertSame($iBottom - $iTop, $oEnzyme->getCleavagePosLower(), "$sId overhang");
                $this->assertSame($iLength, $oEnzyme->getLengthRecognitionPattern(), "$sId length");
                $aForward = array_merge($aForward, self::expand(self::plainSite($sAnnotated)));
            }
            sort($aForward);
            $this->assertSame(array_values(array_unique($aForward)), self::alternatives($oEnzyme->getComputingPattern()), "$sId search pattern");

            $this->assertArrayHasKey("$sId@", $aEnzymes, "$sId has no reverse-orientation row");
            $oReverse = $aEnzymes["$sId@"];
            $aReverse = array_map(fn($s) => trim(self::reverseComplement($s), "."), $aForward);
            $aActual = array_map(fn($s) => trim($s, "."), self::alternatives($oReverse->getComputingPattern()));
            sort($aReverse);
            sort($aActual);
            $this->assertSame(array_values(array_unique($aReverse)), $aActual, "$sId@ search pattern");
            $this->assertSame($oEnzyme->getLengthRecognitionPattern(), $oReverse->getLengthRecognitionPattern(), "$sId@ length");
            $this->assertSame($oEnzyme->getCleavagePosLower(), $oReverse->getCleavagePosLower(), "$sId@ overhang");
        }
    }

    public function testEveryTypeIIbSearchPatternCoversBothOrientations()
    {
        foreach (self::typeIIb() as $sId => $oEnzyme) {
            $sSite = self::plainSite($oEnzyme->getRecognitionPattern());
            $this->assertSame(strlen($sSite), $oEnzyme->getLengthRecognitionPattern(), "$sId length");
            $this->assertSame(2, substr_count($oEnzyme->getRecognitionPattern(), "'"), "$sId top cuts");
            $this->assertSame(2, substr_count($oEnzyme->getRecognitionPattern(), "_"), "$sId bottom cuts");
            $aExpected = array_values(array_unique(array_merge(
                self::expand($sSite),
                self::expand(self::reverseComplement($sSite))
            )));
            sort($aExpected);
            $this->assertSame($aExpected, self::alternatives($oEnzyme->getComputingPattern()), $sId);
        }
    }

    /**
     * Spot values from REBASE for every entry corrected or split out of a neoschizomer group.
     */
    #[DataProvider("rebaseTypeIIProvider")]
    public function testTypeIIEntriesMatchRebase(string $sId, string $sAnnotated, int $iTopCut, int $iOverhang, array $aSamePattern)
    {
        $oEnzyme = self::typeII()[$sId];
        $this->assertSame($sAnnotated, $oEnzyme->getRecognitionPattern());
        $this->assertSame($iTopCut, $oEnzyme->getCleavagePosUpper());
        $this->assertSame($iOverhang, $oEnzyme->getCleavagePosLower());
        $this->assertSame($aSamePattern, explode(",", $oEnzyme->getSamePattern()[0]));
    }

    public static function rebaseTypeIIProvider(): array
    {
        return [
            "SacI GAGCT^C" => ["Psp124BI", "G_AGCT'C", 5, -4, ["Psp124BI", "SacI", "SstI"]],
            "BstKTI GAT^C" => ["BstKTI", "G_AT'C", 3, -2, ["BstKTI"]],
            "AbsI CC^TCGAGG" => ["AbsI", "CC'TCGA_GG", 2, 4, ["AbsI"]],
            "AcoI Y^GGCCR" => ["AcoI", "Y'GGCC_R", 1, 4, ["AcoI"]],
            "AsuNHI G^CTAGC" => ["AsuNHI", "G'CTAG_C", 1, 4, ["AsuNHI", "NheI"]],
            "BspOI GCTAG^C" => ["BspOI", "G_CTAG'C", 5, -4, ["BspOI"]],
            "BisI GC^NGC" => ["BisI", "GC'N_GC", 2, 1, ["BisI", "Fnu4HI", "Fsp4HI", "GluI", "ItaI", "SatI"]],
            "BlsI GCN^GC" => ["BlsI", "GC_N'GC", 3, -1, ["BlsI"]],
            "BmrFI CC^NGG" => ["BmrFI", "CC'N_GG", 2, 1, ["BmrFI"]],
            "BssKI ^CCNGG" => ["BssKI", "'CCNGG_", 0, 5, ["BssKI", "BstSCI", "StyD4I"]],
            "CviAII C^ATG" => ["CviAII", "C'AT_G", 1, 2, ["CviAII"]],
            "NlaIII CATG^" => ["FaeI", "_CATG'", 4, -4, ["FaeI", "Hin1II", "Hsp92II", "NlaIII"]],
            "DinI GGC^GCC" => ["DinI", "GGC'GCC", 3, 0, ["DinI"]],
            "NarI GG^CGCC" => ["Mly113I", "GG'CG_CC", 2, 2, ["Mly113I", "NarI"]],
            "SspDI G^GCGCC" => ["SspDI", "G'GCGC_C", 1, 4, ["SspDI"]],
        ];
    }

    #[DataProvider("rebaseTypeIIsProvider")]
    public function testTypeIIsEntriesMatchRebase(string $sId, string $sAnnotated, int $iTopCut, int $iOverhang, array $aSamePattern)
    {
        $oEnzyme = self::typeIIs()[$sId];
        $this->assertSame($sAnnotated, $oEnzyme->getRecognitionPattern());
        $this->assertSame($iTopCut, $oEnzyme->getCleavagePosUpper());
        $this->assertSame($iOverhang, $oEnzyme->getCleavagePosLower());
        $this->assertSame($aSamePattern, explode(",", $oEnzyme->getSamePattern()[0]));
    }

    public static function rebaseTypeIIsProvider(): array
    {
        return [
            "HpyAV CCTTC(6/5)" => ["HpyAV", "CCTTCNNNNN_N'", 11, -1, ["HpyAV"]],
            "PleI GAGTC(4/5)" => ["PleI", "GAGTCNNNN'N_", 9, 1, ["PleI", "PpsI"]],
            "FokI GGATG(9/13)" => ["FokI", "GGATGNNNNNNNNN'NNNN_", 14, 4, ["FokI"]],
            "BtsCI GGATG(2/0)" => ["BtsCI", "GGATG_NN'", 7, -2, ["BtsCI"]],
            "AbaSI C(11/9)" => ["AbaSI", "CNNNNNNNNN_NN'", 12, -2, ["AbaSI"]],
        ];
    }

    #[DataProvider("rebaseTypeIIbProvider")]
    public function testTypeIIbEntriesMatchRebase(string $sId, string $sAnnotated, array $aSamePattern)
    {
        $oEnzyme = self::typeIIb()[$sId];
        $this->assertSame($sAnnotated, $oEnzyme->getRecognitionPattern());
        $this->assertSame($aSamePattern, $oEnzyme->getSamePattern());
    }

    public static function rebaseTypeIIbProvider(): array
    {
        return [
            "AjuI (7/12)GAANNNNNNNTTGG(11/6)" => ["AjuI#", "_NNNNN'NNNNNNNGAANNNNNNNTTGGNNNNNN_NNNNN'", ["AjuI"]],
            "AlfI (10/12)GCANNNNNNTGC(12/10)" => ["AlfI#", "_NN'NNNNNNNNNNGCANNNNNNTGCNNNNNNNNNN_NN'", ["AlfI"]],
            "ArsI (8/13)GACNNNNNNTTYG(11/6)" => ["ArsI#", "_NNNNN'NNNNNNNNGACNNNNNNTTYGNNNNNN_NNNNN'", ["ArsI"]],
            "CspCI (11/13)CAANNNNNGTGG(12/10)" => ["CspCI#", "_NN'NNNNNNNNNNNCAANNNNNGTGGNNNNNNNNNN_NN'", ["CspCI"]],
            "Hin4I (8/13)GAYNNNNNVTC(13/8)" => ["Hin4I#", "_NNNNN'NNNNNNNNGAYNNNNNVTCNNNNNNNN_NNNNN'", ["Hin4I"]],
        ];
    }

    /**
     * ArsI cuts on both sides of its site (Type IIB) : as a Type IIS entry only one cut was known.
     */
    public function testArsIIsATypeIIbEnzymeOnly()
    {
        $this->assertArrayNotHasKey("ArsI", self::typeIIs());
        $this->assertArrayNotHasKey("ArsI@", self::typeIIs());
        $this->assertArrayHasKey("ArsI#", self::typeIIb());
    }

    /**
     * Dayhoff (1978) PAM250 : symmetric, W/W 17, C/C 12, and W/H -3 (it was +3).
     */
    public function testPam250IsSymmetricWithDayhoffValues()
    {
        require __DIR__ . '/samples/Pam250Matrix.php';
        $aScores = [];
        foreach ($aPam250Matrix as $oCell) {
            $aScores[$oCell->getId()] = $oCell->getValue();
        }

        foreach ($aScores as $sPair => $iScore) {
            $sMirror = strrev($sPair);
            if (isset($aScores[$sMirror])) {
                $this->assertSame($iScore, $aScores[$sMirror], "$sPair vs $sMirror");
            }
        }
        $this->assertSame(17, $aScores["WW"]);
        $this->assertSame(12, $aScores["CC"]);
        $this->assertSame(-3, $aScores["WH"]);
        $this->assertSame(-3, $aScores["HW"]);
    }
}
