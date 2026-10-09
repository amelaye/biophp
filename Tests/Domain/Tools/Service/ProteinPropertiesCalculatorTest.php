<?php
namespace Tests\Domain\Tools\Service;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;
use Amelaye\BioPHP\Domain\Tools\Service\ProteinPropertiesCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
/**
 * Every expected value was computed by Biopython 1.88 (Bio.SeqUtils.IsoelectricPoint and
 * ProtParam.ProteinAnalysis.gravy), run with the three pK sets bioapi serves in place of its own
 * (the residue-specific terminal pK values switched off) and a bisection over pH 0 to 14. Biopython
 * stops its bisection at 1e-4, hence the delta on the isoelectric points.
 */
class ProteinPropertiesCalculatorTest extends TestCase
{
    private const PK = [
        "EMBOSS" => ["NTERMINUS" => 8.6, "K" => 10.8, "R" => 12.5, "H" => 6.5, "CTERMINUS" => 3.6, "D" => 3.9, "E" => 4.1, "C" => 8.5, "Y" => 10.1],
        "DTASelect" => ["NTERMINUS" => 8.0, "K" => 10.0, "R" => 12.0, "H" => 6.5, "CTERMINUS" => 3.1, "D" => 4.4, "E" => 4.4, "C" => 8.5, "Y" => 10.0],
        "Solomon" => ["NTERMINUS" => 9.6, "K" => 10.5, "R" => 12.5, "H" => 6.0, "CTERMINUS" => 2.4, "D" => 3.9, "E" => 4.3, "C" => 8.3, "Y" => 10.1],
    ];

    public static function charges(): array
    {
        return [
            "EMBOSS INGAR pH 3.0" => ["EMBOSS", "INGAR", 3.0, 1.7992374788905487],
            "EMBOSS INGAR pH 7.0" => ["EMBOSS", "INGAR", 7.0, 0.9758914189263085],
            "EMBOSS INGAR pH 10.5" => ["EMBOSS", "INGAR", 10.5, 0.0025318710479579343],
            "EMBOSS PETER pH 3.0" => ["EMBOSS", "PETER", 3.0, 1.6520623666554015],
            "EMBOSS PETER pH 7.0" => ["EMBOSS", "PETER", 7.0, -1.0215938960509812],
            "EMBOSS PETER pH 10.5" => ["EMBOSS", "PETER", 10.5, -1.9974673327380181],
            "EMBOSS ACDEFGHIKLMNPQRSTVWY pH 3.0" => ["EMBOSS", "ACDEFGHIKLMNPQRSTVWY", 3.0, 3.6135147676478194],
            "EMBOSS ACDEFGHIKLMNPQRSTVWY pH 7.0" => ["EMBOSS", "ACDEFGHIKLMNPQRSTVWY", 7.0, 0.186589940553628],
            "EMBOSS ACDEFGHIKLMNPQRSTVWY pH 10.5" => ["EMBOSS", "ACDEFGHIKLMNPQRSTVWY", 10.5, -3.036579826022517],
            "EMBOSS KKKKKK pH 3.0" => ["EMBOSS", "KKKKKK", 3.0, 6.799237384113186],
            "EMBOSS KKKKKK pH 7.0" => ["EMBOSS", "KKKKKK", 7.0, 5.974943795967795],
            "EMBOSS KKKKKK pH 10.5" => ["EMBOSS", "KKKKKK", 10.5, 3.0092694086457024],
            "EMBOSS DDDEEE pH 3.0" => ["EMBOSS", "DDDEEE", 3.0, 0.24302750151970454],
            "EMBOSS DDDEEE pH 7.0" => ["EMBOSS", "DDDEEE", 7.0, -6.017952297937458],
            "EMBOSS DDDEEE pH 10.5" => ["EMBOSS", "DDDEEE", 10.5, -6.987565190966256],
            "EMBOSS MKVLAAGIVGLLLAQPSHA pH 3.0" => ["EMBOSS", "MKVLAAGIVGLLLAQPSHA", 3.0, 2.798921335560215],
            "EMBOSS MKVLAAGIVGLLLAQPSHA pH 7.0" => ["EMBOSS", "MKVLAAGIVGLLLAQPSHA", 7.0, 1.2159891903416487],
            "EMBOSS MKVLAAGIVGLLLAQPSHA pH 10.5" => ["EMBOSS", "MKVLAAGIVGLLLAQPSHA", 10.5, -0.3213277242689099],
            "EMBOSS GGGG pH 3.0" => ["EMBOSS", "GGGG", 3.0, 0.7992374792067762],
            "EMBOSS GGGG pH 7.0" => ["EMBOSS", "GGGG", 7.0, -0.024105418806031342],
            "EMBOSS GGGG pH 10.5" => ["EMBOSS", "GGGG", 10.5, -0.9875671388530322],
            "EMBOSS HHHHCCYY pH 3.0" => ["EMBOSS", "HHHHCCYY", 3.0, 4.797966484615302],
            "EMBOSS HHHHCCYY pH 7.0" => ["EMBOSS", "HHHHCCYY", 7.0, 0.8740126189823674],
            "EMBOSS HHHHCCYY pH 10.5" => ["EMBOSS", "HHHHCCYY", 10.5, -4.39787070074941],
            "DTASelect INGAR pH 3.0" => ["DTASelect", "INGAR", 3.0, 1.5573016328622917],
            "DTASelect INGAR pH 7.0" => ["DTASelect", "INGAR", 7.0, 0.9092067858851505],
            "DTASelect INGAR pH 10.5" => ["DTASelect", "INGAR", 10.5, -0.02750108103773996],
            "DTASelect PETER pH 3.0" => ["DTASelect", "PETER", 3.0, 1.4807286250977825],
            "DTASelect PETER pH 7.0" => ["DTASelect", "PETER", 7.0, -1.085782028780278],
            "DTASelect PETER pH 10.5" => ["DTASelect", "PETER", 10.5, -2.0274994923825322],
            "DTASelect ACDEFGHIKLMNPQRSTVWY pH 3.0" => ["DTASelect", "ACDEFGHIKLMNPQRSTVWY", 3.0, 3.4804091350325126],
            "DTASelect ACDEFGHIKLMNPQRSTVWY pH 7.0" => ["DTASelect", "ACDEFGHIKLMNPQRSTVWY", 7.0, 0.12181961254204676],
            "DTASelect ACDEFGHIKLMNPQRSTVWY pH 10.5" => ["DTASelect", "ACDEFGHIKLMNPQRSTVWY", 10.5, -3.5369923655784388],
            "DTASelect KKKKKK pH 3.0" => ["DTASelect", "KKKKKK", 3.0, 6.557301033862351],
            "DTASelect KKKKKK pH 7.0" => ["DTASelect", "KKKKKK", 7.0, 5.903222779791147],
            "DTASelect KKKKKK pH 10.5" => ["DTASelect", "KKKKKK", 10.5, 0.44467078910622826],
            "DTASelect DDDEEE pH 3.0" => ["DTASelect", "DDDEEE", 3.0, 0.3275826105687635],
            "DTASelect DDDEEE pH 7.0" => ["DTASelect", "DDDEEE", 7.0, -6.075749658211134],
            "DTASelect DDDEEE pH 10.5" => ["DTASelect", "DDDEEE", 10.5, -6.996842885040401],
            "DTASelect MKVLAAGIVGLLLAQPSHA pH 3.0" => ["DTASelect", "MKVLAAGIVGLLLAQPSHA", 3.0, 2.556985406064672],
            "DTASelect MKVLAAGIVGLLLAQPSHA pH 7.0" => ["DTASelect", "MKVLAAGIVGLLLAQPSHA", 7.0, 1.1484708581381928],
            "DTASelect MKVLAAGIVGLLLAQPSHA pH 10.5" => ["DTASelect", "MKVLAAGIVGLLLAQPSHA", 10.5, -0.7564945876529824],
            "DTASelect GGGG pH 3.0" => ["DTASelect", "GGGG", 3.0, 0.5573016338622917],
            "DTASelect GGGG pH 7.0" => ["DTASelect", "GGGG", 7.0, -0.09078321421484847],
            "DTASelect GGGG pH 10.5" => ["DTASelect", "GGGG", 10.5, -0.9968476510060243],
            "DTASelect HHHHCCYY pH 3.0" => ["DTASelect", "HHHHCCYY", 3.0, 4.556030598136473],
            "DTASelect HHHHCCYY pH 7.0" => ["DTASelect", "HHHHCCYY", 7.0, 0.8069242171318869],
            "DTASelect HHHHCCYY pH 10.5" => ["DTASelect", "HHHHCCYY", 10.5, -4.49613956409992],
            "Solomon INGAR pH 3.0" => ["Solomon", "INGAR", 3.0, 1.200759757408294],
            "Solomon INGAR pH 7.0" => ["Solomon", "INGAR", 7.0, 0.9975163632984276],
            "Solomon INGAR pH 10.5" => ["Solomon", "INGAR", 10.5, 0.10191478762238904],
            "Solomon PETER pH 3.0" => ["Solomon", "PETER", 3.0, 1.1053063153398859],
            "Solomon PETER pH 7.0" => ["Solomon", "PETER", 7.0, -0.9985010583601159],
            "Solomon PETER pH 10.5" => ["Solomon", "PETER", 10.5, -1.8980839504637186],
            "Solomon ACDEFGHIKLMNPQRSTVWY pH 3.0" => ["Solomon", "ACDEFGHIKLMNPQRSTVWY", 3.0, 3.0402131426941614],
            "Solomon ACDEFGHIKLMNPQRSTVWY pH 7.0" => ["Solomon", "ACDEFGHIKLMNPQRSTVWY", 7.0, 0.04237389454641294],
            "Solomon ACDEFGHIKLMNPQRSTVWY pH 10.5" => ["Solomon", "ACDEFGHIKLMNPQRSTVWY", 10.5, -3.1070354471632156],
            "Solomon KKKKKK pH 3.0" => ["Solomon", "KKKKKK", 3.0, 6.200759567987868],
            "Solomon KKKKKK pH 7.0" => ["Solomon", "KKKKKK", 7.0, 5.995622758780309],
            "Solomon KKKKKK pH 10.5" => ["Solomon", "KKKKKK", 10.5, 2.111815777721399],
            "Solomon DDDEEE pH 3.0" => ["Solomon", "DDDEEE", 3.0, -0.2778677147124411],
            "Solomon DDDEEE pH 7.0" => ["Solomon", "DDDEEE", 7.0, -5.99412551358722],
            "Solomon DDDEEE pH 10.5" => ["Solomon", "DDDEEE", 10.5, -6.888181575842022],
            "Solomon MKVLAAGIVGLLLAQPSHA pH 3.0" => ["Solomon", "MKVLAAGIVGLLLAQPSHA", 3.0, 2.199760725102745],
            "Solomon MKVLAAGIVGLLLAQPSHA pH 7.0" => ["Solomon", "MKVLAAGIVGLLLAQPSHA", 7.0, 1.088112488677549],
            "Solomon MKVLAAGIVGLLLAQPSHA pH 10.5" => ["Solomon", "MKVLAAGIVGLLLAQPSHA", 10.5, -0.38815260050196765],
            "Solomon GGGG pH 3.0" => ["Solomon", "GGGG", 3.0, 0.2007597577245216],
            "Solomon GGGG pH 7.0" => ["Solomon", "GGGG", 7.0, -0.0024804744339123053],
            "Solomon GGGG pH 10.5" => ["Solomon", "GGGG", 10.5, -0.8881842222786009],
            "Solomon HHHHCCYY pH 3.0" => ["Solomon", "HHHHCCYY", 3.0, 4.196753571168449],
            "Solomon HHHHCCYY pH 7.0" => ["Solomon", "HHHHCCYY", 7.0, 0.2641150515777051],
            "Solomon HHHHCCYY pH 10.5" => ["Solomon", "HHHHCCYY", 10.5, -4.306023212587598],
        ];
    }

    #[DataProvider("charges")]
    public function testNetChargeMatchesBiopython(string $sSet, string $sProtein, float $fPh, float $fExpected)
    {
        $fCharge = (new ProteinPropertiesCalculator())->netCharge(new AminoAcidSequence($sProtein), $fPh, self::PK[$sSet]);

        $this->assertEqualsWithDelta($fExpected, $fCharge, 1e-9);
    }

    public static function isoelectricPoints(): array
    {
        return [
            "EMBOSS INGAR" => ["EMBOSS", "INGAR", 10.55001449584961],
            "EMBOSS PETER" => ["EMBOSS", "PETER", 4.258121490478516],
            "EMBOSS ACDEFGHIKLMNPQRSTVWY" => ["EMBOSS", "ACDEFGHIKLMNPQRSTVWY", 7.356563568115234],
            "EMBOSS KKKKKK" => ["EMBOSS", "KKKKKK", 11.499622344970703],
            "EMBOSS DDDEEE" => ["EMBOSS", "DDDEEE", 3.140979766845703],
            "EMBOSS MKVLAAGIVGLLLAQPSHA" => ["EMBOSS", "MKVLAAGIVGLLLAQPSHA", 9.701984405517578],
            "EMBOSS GGGG" => ["EMBOSS", "GGGG", 6.099979400634766],
            "EMBOSS HHHHCCYY" => ["EMBOSS", "HHHHCCYY", 7.579532623291016],
            "DTASelect INGAR" => ["DTASelect", "INGAR", 9.999988555908203],
            "DTASelect PETER" => ["DTASelect", "PETER", 4.437938690185547],
            "DTASelect ACDEFGHIKLMNPQRSTVWY" => ["DTASelect", "ACDEFGHIKLMNPQRSTVWY", 7.174396514892578],
            "DTASelect KKKKKK" => ["DTASelect", "KKKKKK", 10.700031280517578],
            "DTASelect DDDEEE" => ["DTASelect", "DDDEEE", 3.262531280517578],
            "DTASelect MKVLAAGIVGLLLAQPSHA" => ["DTASelect", "MKVLAAGIVGLLLAQPSHA", 9.008136749267578],
            "DTASelect GGGG" => ["DTASelect", "GGGG", 5.550006866455078],
            "DTASelect HHHHCCYY" => ["DTASelect", "HHHHCCYY", 7.459102630615234],
            "Solomon INGAR" => ["Solomon", "INGAR", 11.049999237060547],
            "Solomon PETER" => ["Solomon", "PETER", 4.310512542724609],
            "Solomon ACDEFGHIKLMNPQRSTVWY" => ["Solomon", "ACDEFGHIKLMNPQRSTVWY", 7.139629364013672],
            "Solomon KKKKKK" => ["Solomon", "KKKKKK", 11.211551666259766],
            "Solomon DDDEEE" => ["Solomon", "DDDEEE", 2.775310516357422],
            "Solomon MKVLAAGIVGLLLAQPSHA" => ["Solomon", "MKVLAAGIVGLLLAQPSHA", 10.05008316040039],
            "Solomon GGGG" => ["Solomon", "GGGG", 6.000003814697266],
            "Solomon HHHHCCYY" => ["Solomon", "HHHHCCYY", 7.301181793212891],
        ];
    }

    #[DataProvider("isoelectricPoints")]
    public function testIsoelectricPointMatchesBiopython(string $sSet, string $sProtein, float $fExpected)
    {
        $fPi = (new ProteinPropertiesCalculator())->isoelectricPoint(new AminoAcidSequence($sProtein), self::PK[$sSet]);

        $this->assertEqualsWithDelta($fExpected, $fPi, 2e-4);
    }

    public function testTheChargeIsZeroAtTheIsoelectricPoint()
    {
        $oCalculator = new ProteinPropertiesCalculator();
        $oProtein = new AminoAcidSequence("MKVLAAGIVGLLLAQPSHA");
        foreach (self::PK as $aPk) {
            $fPi = $oCalculator->isoelectricPoint($oProtein, $aPk);

            $this->assertEqualsWithDelta(0.0, $oCalculator->netCharge($oProtein, $fPi, $aPk), 1e-6);
        }
    }

    public static function gravies(): array
    {
        return [
            "INGAR" => ["INGAR", -0.42000000000000004],
            "PETER" => ["PETER", -2.7600000000000002],
            "ACDEFGHIKLMNPQRSTVWY" => ["ACDEFGHIKLMNPQRSTVWY", -0.49000000000000005],
            "KKKKKK" => ["KKKKKK", -3.9],
            "DDDEEE" => ["DDDEEE", -3.5],
            "MKVLAAGIVGLLLAQPSHA" => ["MKVLAAGIVGLLLAQPSHA", 1.2315789473684209],
            "GGGG" => ["GGGG", -0.4],
            "HHHHCCYY" => ["HHHHCCYY", -1.3],
        ];
    }

    #[DataProvider("gravies")]
    public function testGravyMatchesBiopython(string $sProtein, float $fExpected)
    {
        $this->assertEqualsWithDelta($fExpected, (new ProteinPropertiesCalculator())->gravy(new AminoAcidSequence($sProtein)), 1e-12);
    }

    /**
     * Biopython has no value for B, Z, X..., and fails on them. They are left out of the mean, which
     * the length does not count either : counting them as zero would pull the score towards neutral.
     */
    public function testGravyLeavesOutTheResiduesWithoutAValue()
    {
        $oCalculator = new ProteinPropertiesCalculator();

        $this->assertEqualsWithDelta(
            $oCalculator->gravy(new AminoAcidSequence("INGAR")),
            $oCalculator->gravy(new AminoAcidSequence("INXGABRZ*")),
            1e-12
        );
    }

    public function testGravyOfAProteinWithoutAnyScoredResidueThrows()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ProteinPropertiesCalculator())->gravy(new AminoAcidSequence("XXBZ"));
    }

    public function testAnEmptyProteinThrows()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ProteinPropertiesCalculator())->isoelectricPoint(new AminoAcidSequence(""), self::PK["EMBOSS"]);
    }

    public function testAPkSetWithoutAGroupThrows()
    {
        $aPk = self::PK["EMBOSS"];
        unset($aPk["Y"]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"Y"');

        (new ProteinPropertiesCalculator())->netCharge(new AminoAcidSequence("INGAR"), 7.0, $aPk);
    }

    public function testThePkKeysMayBeInAnyCase()
    {
        $oCalculator = new ProteinPropertiesCalculator();
        $oProtein = new AminoAcidSequence("INGAR");

        $this->assertSame(
            $oCalculator->netCharge($oProtein, 7.0, self::PK["EMBOSS"]),
            $oCalculator->netCharge($oProtein, 7.0, array_change_key_case(self::PK["EMBOSS"], CASE_LOWER))
        );
    }

    public function testAProteinOfLysinesIsPositiveAndOneOfAspartatesNegativeAtNeutralPh()
    {
        $oCalculator = new ProteinPropertiesCalculator();

        $this->assertGreaterThan(5.0, $oCalculator->netCharge(new AminoAcidSequence("KKKKKK"), 7.0, self::PK["EMBOSS"]));
        $this->assertLessThan(-5.0, $oCalculator->netCharge(new AminoAcidSequence("DDDDDD"), 7.0, self::PK["EMBOSS"]));
    }
}
