<?php
namespace Tests\Api;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The reference data of bioapi is copied under Tests/**\/samples, one copy for the API tests and
 * one for each domain test that needs part of it. A correction goes to every copy together, and
 * BiologicalReferenceDataTest only reads the Api one : this checks that the others say the same,
 * row for row, whatever the order the rows are written in.
 */
class SampleMirrorsTest extends TestCase
{
    private const DOMAIN_COPIES = [
        "Domain/Sequence/Service/samples",
        "Domain/Tools/Service/samples",
    ];

    /**
     * The sample files that are copied, and the variable each one fills with its objects.
     */
    private const SAMPLES = [
        "Aminos.php" => "aAminosObjects",
        "Elements.php" => "aElementsObjects",
        "Nucleotids.php" => "aNucleoObjects",
        "ProteinReductions.php" => "aReductions",
        "TypeIIEndonucleases.php" => "aTypeIIEndonucleases",
        "Type2sEndonucleases.php" => "aTypeIIsEndonucleases",
        "Type2bEndonucleases.php" => "aTypeIIbEndonucleases",
    ];

    /**
     * @return  string[]    The rows of a sample file, each one serialized, in a canonical order
     */
    private static function rows(string $sPath, string $sVariable): array
    {
        $fLoad = function (string $sFile) {
            require $sFile;

            return get_defined_vars();
        };
        $aVars = $fLoad($sPath);
        self::assertArrayHasKey($sVariable, $aVars, $sPath);

        $aRows = array_map('serialize', $aVars[$sVariable]);
        sort($aRows);

        return $aRows;
    }

    public static function copies(): array
    {
        $aCases = [];
        foreach (self::DOMAIN_COPIES as $sDirectory) {
            foreach (self::SAMPLES as $sFile => $sVariable) {
                if (is_file(__DIR__ . "/../" . $sDirectory . "/" . $sFile)) {
                    $aCases[$sDirectory . "/" . $sFile] = [$sDirectory, $sFile, $sVariable];
                }
            }
        }

        return $aCases;
    }

    #[DataProvider("copies")]
    public function testADomainCopyHoldsTheRowsOfTheApiSample(string $sDirectory, string $sFile, string $sVariable)
    {
        $this->assertSame(
            self::rows(__DIR__ . "/samples/" . $sFile, $sVariable),
            self::rows(__DIR__ . "/../" . $sDirectory . "/" . $sFile, $sVariable),
            "$sDirectory/$sFile differs from Tests/Api/samples/$sFile"
        );
    }

    public function testEveryDomainCopyIsCoveredByThisCheck()
    {
        foreach (self::DOMAIN_COPIES as $sDirectory) {
            $aFiles = array_map('basename', glob(__DIR__ . "/../" . $sDirectory . "/*.php"));
            $this->assertEqualsCanonicalizing(
                [],
                array_values(array_diff($aFiles, array_keys(self::SAMPLES))),
                "$sDirectory holds a sample this test does not compare"
            );
        }
    }
}
