<?php
namespace Tests\Domain\Tools\Service;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\ElementApi;
use Amelaye\BioPHP\Api\NucleotidApi;
use Amelaye\BioPHP\Domain\Sequence\Builder\SequenceBuilder;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Service\OrfFinder;
use PHPUnit\Framework\TestCase;

/**
 * Every fixture's expected ORF(s) were derived by hand, translating each of the 6 frames codon by
 * codon under the standard genetic code (ATG=Met, TAG/TGA/TAA=Stop, and other textbook codon facts),
 * and for reverse-strand cases independently re-deriving the reverse complement base by base before
 * mapping coordinates back with OrfFinder's documented formula. See each test's own comment for the
 * derivation.
 */
class OrfFinderTest extends TestCase
{
    private $finder;

    public function setUp(): void
    {
        require 'samples/Aminos.php';
        require 'samples/Nucleotids.php';
        require 'samples/Elements.php';

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

        $this->finder = new OrfFinder($sequenceBuilder);
    }

    /**
     * "ATGAAATAG" : frame +1 reads ATG(Met) AAA(Lys) TAG(Stop) - one complete ORF, start=1, end=9
     * (stop included), peptide "MK". Every other one of the 6 frames was hand-checked too (forward
     * frames 2/3 and all 3 reverse frames on the reverse complement "CTATTTCAT") and contains no Met
     * at all, so this is the only ORF found across the whole sequence.
     */
    public function testFindsASingleCompleteForwardOrf()
    {
        $aOrfs = $this->finder->findOrfs(new DnaSequence("ATGAAATAG"));

        $this->assertCount(1, $aOrfs);
        $this->assertEquals(1, $aOrfs[0]->getFrame());
        $this->assertEquals(1, $aOrfs[0]->getStart());
        $this->assertEquals(9, $aOrfs[0]->getEnd());
        $this->assertEquals("MK", $aOrfs[0]->getPeptide());
        $this->assertTrue($aOrfs[0]->hasStopCodon());
        $this->assertFalse($aOrfs[0]->isReverseStrand());
    }

    public function testAMinimumProteinLengthAboveTheOrfsLengthExcludesIt()
    {
        $aOrfs = $this->finder->findOrfs(new DnaSequence("ATGAAATAG"), 3);

        $this->assertCount(0, $aOrfs);
    }

    public function testAMinimumProteinLengthExactlyAtTheOrfsLengthIncludesIt()
    {
        $aOrfs = $this->finder->findOrfs(new DnaSequence("ATGAAATAG"), 2);

        $this->assertCount(1, $aOrfs);
    }

    /**
     * "ATGAAACCC" : frame +1 reads ATG(Met) AAA(Lys) CCC(Pro) with no stop codon at all - an ORF
     * still "open" at the end of the sequence, a real and common case at a linear sequence's edge.
     * Every other frame was hand-checked (including the reverse complement "GGGTTTCAT") and contains
     * no Met, so this is the only ORF found.
     */
    public function testAnOrfWithNoStopCodonIsStillReportedAsOpen()
    {
        $aOrfs = $this->finder->findOrfs(new DnaSequence("ATGAAACCC"));

        $this->assertCount(1, $aOrfs);
        $this->assertEquals(1, $aOrfs[0]->getStart());
        $this->assertEquals(9, $aOrfs[0]->getEnd());
        $this->assertEquals("MKP", $aOrfs[0]->getPeptide());
        $this->assertFalse($aOrfs[0]->hasStopCodon());
    }

    /**
     * Original sequence "CTACAT" was chosen because its reverse complement is exactly "ATGTAG" :
     * complement("CTACAT")="GATGTA", reversed="ATGTAG" - a single Met+Stop ORF spanning the entire
     * 6-base reverse complement. Reverse-frame coordinates are mapped back to the ORIGINAL sequence
     * using OrfFinder's documented formula ; independently re-checking it here : the ORF covers
     * revcomp positions [0,5] (all 6 bases), which are original positions [0,5] read backwards, i.e.
     * the whole original sequence, so start=1, end=6 is the only geometrically possible answer.
     * Every other frame (forward frames 1-3 of "CTACAT", reverse frames -2/-3) was hand-checked and
     * contains no Met, so this is the only ORF found.
     */
    public function testFindsAReverseStrandOrfWithCoordinatesInOriginalSequenceSpace()
    {
        $aOrfs = $this->finder->findOrfs(new DnaSequence("CTACAT"));

        $this->assertCount(1, $aOrfs);
        $this->assertEquals(-1, $aOrfs[0]->getFrame());
        $this->assertEquals(1, $aOrfs[0]->getStart());
        $this->assertEquals(6, $aOrfs[0]->getEnd());
        $this->assertEquals("M", $aOrfs[0]->getPeptide());
        $this->assertTrue($aOrfs[0]->hasStopCodon());
        $this->assertTrue($aOrfs[0]->isReverseStrand());
    }

    public function testASequenceWithNoStartCodonAnywhereFindsNoOrf()
    {
        $aOrfs = $this->finder->findOrfs(new DnaSequence("CCCGGGCCCGGG"));

        $this->assertCount(0, $aOrfs);
    }

    /**
     * @param   \Amelaye\BioPHP\Domain\Tools\ValueObject\OpenReadingFrame[]   $aOrfs
     * @return  string[]    "start-end peptide" of the ORFs of the first forward frame
     */
    private static function firstFrame(array $aOrfs): array
    {
        $aFirstFrame = [];
        foreach ($aOrfs as $oOrf) {
            if ($oOrf->getFrame() === 1) {
                $aFirstFrame[] = $oOrf->getStart() . "-" . $oOrf->getEnd() . " " . $oOrf->getPeptide();
            }
        }

        return $aFirstFrame;
    }

    /**
     * AGA is Arg in the standard code and a stop in the vertebrate mitochondrial one (table 2), where
     * ATA, Ile in the standard code, is a Met : the same DNA has other ORFs under each.
     */
    public function testAnOrfFollowsTheGeneticCodeItIsSearchedUnder()
    {
        $oSequence = new DnaSequence("ATGAAAAGAGGGTAA");

        $this->assertSame(["1-15 MKRG"], self::firstFrame($this->finder->findOrfs($oSequence)));
        $this->assertSame(["1-9 MK"], self::firstFrame($this->finder->findOrfs($oSequence, 1, 2)));

        $oSequence = new DnaSequence("ATAAAATAA");
        $this->assertSame([], self::firstFrame($this->finder->findOrfs($oSequence, 1, 1)));
        $this->assertSame(["1-9 MK"], self::firstFrame($this->finder->findOrfs($oSequence, 1, 2)));
    }

    public function testTheStandardCodeIsTheDefault()
    {
        $oSequence = new DnaSequence("ATGAAAAGAGGGTAA");

        $this->assertEquals($this->finder->findOrfs($oSequence, 1, 1), $this->finder->findOrfs($oSequence));
    }

    public function testAnUnsupportedGeneticCodeIsRefused()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported genetic code table 27");

        $this->finder->findOrfs(new DnaSequence("ATGAAATAA"), 1, 27);
    }
}
