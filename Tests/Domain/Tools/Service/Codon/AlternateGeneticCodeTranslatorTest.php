<?php
namespace Tests\Domain\Tools\Service\Codon;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\ElementApi;
use Amelaye\BioPHP\Api\NucleotidApi;
use Amelaye\BioPHP\Domain\Sequence\Builder\SequenceBuilder;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use Amelaye\BioPHP\Domain\Tools\Service\Codon\AlternateGeneticCodeTranslator;
use Amelaye\BioPHP\Domain\Tools\ValueObject\GeneticCodeTable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The four codons this test exercises (AGA, AGG, ATA, TGA) are the textbook example of how the
 * vertebrate mitochondrial genetic code (NCBI table 2) differs from the standard one : AGA/AGG
 * become stop codons instead of Arginine, and ATA/TGA are reassigned to Met/Trp instead of
 * Ile/Stop - well-established molecular biology, not independently re-derived here.
 */
class AlternateGeneticCodeTranslatorTest extends TestCase
{
    private $translator;

    public function setUp(): void
    {
        require __DIR__ . '/../samples/Aminos.php';
        require __DIR__ . '/../samples/Nucleotids.php';
        require __DIR__ . '/../samples/Elements.php';

        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = \JMS\Serializer\SerializerBuilder::create()->build();

        $apiAminoMock = $this->getMockBuilder(AminoApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getAminos'])
            ->getMock();
        $apiAminoMock->method("getAminos")->willReturn($aAminosObjects);

        $apiNucleoMock = $this->getMockBuilder(NucleotidApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getNucleotids'])
            ->getMock();
        $apiNucleoMock->method("getNucleotids")->willReturn($aNucleoObjects);

        $apiElementsMock = $this->getMockBuilder(ElementApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getElements', 'getElement'])
            ->getMock();
        $apiElementsMock->method("getElements")->willReturn($aElementsObjects);
        $apiElementsMock->method("getElement")->willReturn($aElementsObjects[5]);

        $sequenceManager = new SequenceManager($apiAminoMock, $apiNucleoMock, $apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);

        $this->translator = new AlternateGeneticCodeTranslator($sequenceBuilder);
    }

    public function testStandardTableMatchesTheOrdinaryStandardGeneticCode()
    {
        $this->assertEquals("R", $this->translator->translateCodon("AGA", GeneticCodeTable::STANDARD));
        $this->assertEquals("I", $this->translator->translateCodon("ATA", GeneticCodeTable::STANDARD));
        $this->assertEquals("*", $this->translator->translateCodon("TGA", GeneticCodeTable::STANDARD));
    }

    public function testVertebrateMitochondrialReassignsAgaAndAggToStop()
    {
        $this->assertEquals("*", $this->translator->translateCodon("AGA", GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL));
        $this->assertEquals("*", $this->translator->translateCodon("AGG", GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL));
    }

    public function testVertebrateMitochondrialReassignsAtaToMetAndTgaToTrp()
    {
        $this->assertEquals("M", $this->translator->translateCodon("ATA", GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL));
        $this->assertEquals("W", $this->translator->translateCodon("TGA", GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL));
    }

    /**
     * A codon outside the four reassigned ones is identical under both tables - TTT (Phe) is
     * untouched by the vertebrate mitochondrial overlay.
     */
    public function testACodonNotInTheOverlayFallsBackToTheStandardTranslation()
    {
        $this->assertEquals(
            $this->translator->translateCodon("TTT", GeneticCodeTable::STANDARD),
            $this->translator->translateCodon("TTT", GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL)
        );
    }

    public function testRejectsAnUnsupportedTable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported genetic code table 99");

        $this->translator->translateCodon("ATG", 99);
    }

    public function testVertebrateMitochondrialReadsRnaCodonsTheSameWay()
    {
        // The overlay is keyed on DNA codons : AUA, UGA and AGA used to fall through to the
        // standard code (Ile, Stop, Arg) as if the table did not apply to an mRNA.
        $this->assertEquals("M", $this->translator->translateCodon("AUA", GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL));
        $this->assertEquals("W", $this->translator->translateCodon("UGA", GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL));
        $this->assertEquals("*", $this->translator->translateCodon("aga", GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL));
        $this->assertEquals("*", $this->translator->translateCodon("AGG", GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL));
    }

    /**
     * An ambiguous codon was always read under the standard code : AGR is Arg there, and a stop in the
     * vertebrate mitochondrial code, where AGA and AGG both are ; a mitochondrial CDS read through its
     * terminator. NCBI table 2 : TGR is Trp (TGA, TGG), ATR is Met (ATA, ATG).
     */
    public function testAnAmbiguousCodonIsReadUnderTheRequestedTable()
    {
        $iTable = GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL;

        $this->assertEquals("*", $this->translator->translateCodon("AGR", $iTable));
        $this->assertEquals("W", $this->translator->translateCodon("TGR", $iTable));
        $this->assertEquals("M", $this->translator->translateCodon("ATR", $iTable));
        // AGT/AGC are Ser and AGA/AGG stops : the codons covered disagree
        $this->assertEquals("X", $this->translator->translateCodon("AGN", $iTable));
        $this->assertEquals("X", $this->translator->translateCodon("NNN", $iTable));
        $this->assertEquals("W", $this->translator->translateCodon("UGR", $iTable));
        // The standard code keeps its own reading
        $this->assertEquals("R", $this->translator->translateCodon("AGR", GeneticCodeTable::STANDARD));
    }

    /**
     * Each table, as a string of the amino acid of the 64 codons in TCAG order (TTT, TTC, TTA, TTG,
     * TCT...), "*" for a stop : read from Biopython 1.88's CodonTable.unambiguous_dna_by_id, which
     * agrees with NCBI's gc.prt on every one of them.
     */
    public static function tables(): array
    {
        return [
            1 => [1, "FFLLSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            2 => [2, "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIMMTTTTNNKKSS**VVVVAAAADDEEGGGG"],
            3 => [3, "FFLLSSSSYY**CCWWTTTTPPPPHHQQRRRRIIMMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            4 => [4, "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            5 => [5, "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIMMTTTTNNKKSSSSVVVVAAAADDEEGGGG"],
            6 => [6, "FFLLSSSSYYQQCC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            9 => [9, "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIIMTTTTNNNKSSSSVVVVAAAADDEEGGGG"],
            10 => [10, "FFLLSSSSYY**CCCWLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            11 => [11, "FFLLSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            12 => [12, "FFLLSSSSYY**CC*WLLLSPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            13 => [13, "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIMMTTTTNNKKSSGGVVVVAAAADDEEGGGG"],
            14 => [14, "FFLLSSSSYYY*CCWWLLLLPPPPHHQQRRRRIIIMTTTTNNNKSSSSVVVVAAAADDEEGGGG"],
            15 => [15, "FFLLSSSSYY*QCC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            16 => [16, "FFLLSSSSYY*LCC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            21 => [21, "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIMMTTTTNNNKSSSSVVVVAAAADDEEGGGG"],
            22 => [22, "FFLLSS*SYY*LCC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            23 => [23, "FF*LSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            24 => [24, "FFLLSSSSYY**CCWWLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSSKVVVVAAAADDEEGGGG"],
            25 => [25, "FFLLSSSSYY**CCGWLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            26 => [26, "FFLLSSSSYY**CC*WLLLAPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            29 => [29, "FFLLSSSSYYYYCC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            30 => [30, "FFLLSSSSYYEECC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            32 => [32, "FFLLSSSSYY*WCC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG"],
            33 => [33, "FFLLSSSSYYY*CCWWLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSSKVVVVAAAADDEEGGGG"],
        ];
    }

    #[DataProvider("tables")]
    public function testEveryCodonOfEveryTableMatchesBiopython(int $iTable, string $sExpected)
    {
        $sCodons = [];
        foreach (str_split("TCAG") as $sFirst) {
            foreach (str_split("TCAG") as $sSecond) {
                foreach (str_split("TCAG") as $sThird) {
                    $sCodons[] = $sFirst . $sSecond . $sThird;
                }
            }
        }

        $sTranslated = "";
        foreach ($sCodons as $sCodon) {
            $sTranslated .= $this->translator->translateCodon($sCodon, $iTable);
        }

        $this->assertSame($sExpected, $sTranslated, "table $iTable");
    }

    public static function names(): array
    {
        return [
            1 => [1, "Standard"],
            2 => [2, "Vertebrate Mitochondrial"],
            3 => [3, "Yeast Mitochondrial"],
            4 => [4, "Mold Mitochondrial; Protozoan Mitochondrial; Coelenterate
 Mitochondrial; Mycoplasma; Spiroplasma"],
            5 => [5, "Invertebrate Mitochondrial"],
            6 => [6, "Ciliate Nuclear; Dasycladacean Nuclear; Hexamita Nuclear"],
            9 => [9, "Echinoderm Mitochondrial; Flatworm Mitochondrial"],
            10 => [10, "Euplotid Nuclear"],
            11 => [11, "Bacterial, Archaeal and Plant Plastid"],
            12 => [12, "Alternative Yeast Nuclear"],
            13 => [13, "Ascidian Mitochondrial"],
            14 => [14, "Alternative Flatworm Mitochondrial"],
            15 => [15, "Blepharisma Macronuclear"],
            16 => [16, "Chlorophycean Mitochondrial"],
            21 => [21, "Trematode Mitochondrial"],
            22 => [22, "Scenedesmus obliquus Mitochondrial"],
            23 => [23, "Thraustochytrium Mitochondrial"],
            24 => [24, "Rhabdopleuridae Mitochondrial"],
            25 => [25, "Candidate Division SR1 and Gracilibacteria"],
            26 => [26, "Pachysolen tannophilus Nuclear"],
            29 => [29, "Mesodinium Nuclear"],
            30 => [30, "Peritrich Nuclear"],
            32 => [32, "Balanophoraceae Plastid"],
            33 => [33, "Cephalodiscidae Mitochondrial"],
        ];
    }

    #[DataProvider("names")]
    public function testEverySupportedTableIsNamedAsNcbiNamesIt(int $iTable, string $sName)
    {
        $this->assertContains($iTable, GeneticCodeTable::SUPPORTED_TABLES);
        $this->assertSame($sName, GeneticCodeTable::NAMES[$iTable]);
    }

    /**
     * Their UGA (and for 28 and 31 their UAA and UAG) is a stop or an amino acid by position : one
     * reading per codon cannot say, so these tables are refused rather than read wrongly.
     */
    public function testTablesWhoseStopCodonsDependOnTheirContextAreRefused()
    {
        foreach ([27, 28, 31] as $iTable) {
            try {
                $this->translator->translateCodon("TGA", $iTable);
                $this->fail("Table $iTable should be refused.");
            } catch (\InvalidArgumentException $ex) {
                $this->assertStringContainsString("Unsupported genetic code table $iTable", $ex->getMessage());
            }
        }
        $this->assertCount(24, GeneticCodeTable::SUPPORTED_TABLES);
    }

    public function testAnAmbiguousCodonIsReadUnderTheNewerTablesToo()
    {
        // table 25 (SR1 and Gracilibacteria) reads TGA as Gly : TGR covers TGA (Gly) and TGG (Trp)
        $this->assertSame("X", $this->translator->translateCodon("TGR", 25));
        // table 4 reads TGA as Trp : TGR is Trp
        $this->assertSame("W", $this->translator->translateCodon("TGR", 4));
    }
}
