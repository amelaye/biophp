<?php
namespace Tests\Domain\Cloning\Service;

use Amelaye\BioPHP\Api\TypeIIbEndonucleaseApi;
use Amelaye\BioPHP\Api\TypeIIEndonucleaseApi;
use Amelaye\BioPHP\Api\TypeIIsEndonucleaseApi;
use Amelaye\BioPHP\Domain\Cloning\Service\LinearRestrictionDigestManager;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;
use Amelaye\BioPHP\Domain\Sequence\Service\RestrictionEnzymeCatalog;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use PHPUnit\Framework\TestCase;

/**
 * The oracle is Biopython 1.88's Bio.Restriction (tests/samples/BiopythonLinearDigests.php) : the
 * cuts and the fragments of 15 enzymes of the three families on 60 sequences, a good part of them
 * with a site against an end, where a Type IIs enzyme cuts outside the sequence. The enzymes come
 * from the library's own catalog, so this checks its cleavage data against Biopython's as well.
 */
class LinearRestrictionDigestManagerTest extends TestCase
{
    private LinearRestrictionDigestManager $manager;

    private RestrictionEnzymeCatalog $catalog;

    public function setUp(): void
    {
        $this->manager = new LinearRestrictionDigestManager();

        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = \JMS\Serializer\SerializerBuilder::create()->build();

        require __DIR__ . '/../../Sequence/Service/samples/TypeIIEndonucleases.php';
        require __DIR__ . '/../../Sequence/Service/samples/Type2bEndonucleases.php';
        require __DIR__ . '/../../Sequence/Service/samples/Type2sEndonucleases.php';

        $typeIIApiMock = $this->getMockBuilder(TypeIIEndonucleaseApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getTypeIIEndonucleases'])
            ->getMock();
        $typeIIApiMock->method("getTypeIIEndonucleases")->willReturn($aTypeIIEndonucleases);
        $typeIIbApiMock = $this->getMockBuilder(TypeIIbEndonucleaseApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getTypeIIbEndonucleases'])
            ->getMock();
        $typeIIbApiMock->method("getTypeIIbEndonucleases")->willReturn($aTypeIIbEndonucleases);
        $typeIIsApiMock = $this->getMockBuilder(TypeIIsEndonucleaseApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getTypeIIsEndonucleases'])
            ->getMock();
        $typeIIsApiMock->method("getTypeIIsEndonucleases")->willReturn($aTypeIIsEndonucleases);

        $this->catalog = new RestrictionEnzymeCatalog($typeIIApiMock, $typeIIbApiMock, $typeIIsApiMock);
    }

    /**
     * A Type IIb enzyme cuts on both sides of its site : AjuI, AlfI, BaeI... made one cut, and a
     * second one, phantom, from the same site found again on the other strand. Biopython locates
     * both cuts (search) though it does not digest with them.
     */
    public function testATypeIIbSiteIsCutOnBothSidesAtBiopythonsPositions()
    {
        $aRows = require __DIR__ . '/samples/BiopythonTypeIIbSites.php';

        foreach ($aRows as $i => $aRow) {
            $oEnzyme = $this->catalog->getByName($aRow["enzyme"]);
            $oResult = $this->manager->digest(new DnaSequence($aRow["sequence"]), [$oEnzyme]);

            $aUppers = array_map(fn($oCut) => $oCut->getUpperCutPosition() + 1, $oResult->getCuts());
            sort($aUppers);
            $this->assertSame($aRow["cuts"], $aUppers, "row $i, " . $aRow["enzyme"]);
            $this->assertCount(3, $oResult->getFragments(), "row $i, " . $aRow["enzyme"]);
            foreach ($oResult->getCuts() as $oCut) {
                $this->assertSame(
                    $oEnzyme->getCleavagePositionLower(),
                    $oCut->getLowerCutPosition() - $oCut->getUpperCutPosition(),
                    "row $i, " . $aRow["enzyme"] . " : the overhang"
                );
            }
        }
        $this->assertCount(91, $aRows);
    }

    public function testTheCutsAndFragmentsAreBiopythons()
    {
        $aRows = require __DIR__ . '/samples/BiopythonLinearDigests.php';
        $iChecked = 0;

        foreach ($aRows as $i => $aRow) {
            foreach (["EcoRI", "KpnI", "SmaI", "EcoRV", "AatII", "HinfI", "BstXI", "BsaI", "BbsI", "FokI", "MmeI", "BsmBI", "HhaI", "BglI", "AciI"] as $sName) {
                $oResult = $this->manager->digest(new DnaSequence($aRow["sequence"]), [$this->catalog->getByName($sName)]);

                $aCuts = array_values(array_unique(array_map(fn($oCut) => $oCut->getUpperCutPosition(), $oResult->getCuts())));
                sort($aCuts);
                $aExpected = $aRow["digests"][$sName] ?? ["cuts" => [], "fragments" => []];

                $this->assertSame($aExpected["cuts"], $aCuts, "sequence $i, $sName : cuts");
                $this->assertSame(
                    $aExpected["fragments"],
                    array_map(fn($oFragment) => $oFragment->getSequence()->getValue(), $oResult->getFragments()),
                    "sequence $i, $sName : fragments"
                );
                $iChecked++;
            }
        }

        $this->assertSame(900, $iChecked);
    }

    /**
     * EcoRI cuts G^AATTC and leaves a 5' AATT overhang : positions worked out by hand.
     */
    public function testAFiveprimeOverhangIsReadFromTheSequence()
    {
        $oResult = $this->manager->digest(new DnaSequence("AAGAATTCTT"), [$this->catalog->getByName("EcoRI")]);

        $this->assertCount(1, $oResult->getCuts());
        $oCut = $oResult->getCuts()[0];
        $this->assertSame([2, 3, 7], [$oCut->getRecognitionPosition(), $oCut->getUpperCutPosition(), $oCut->getLowerCutPosition()]);
        $this->assertSame(RestrictionEnd::FIVE_PRIME, $oCut->getEnd()->getType());
        $this->assertSame("AATT", $oCut->getEnd()->getOverhangSequence());

        $aFragments = $oResult->getFragments();
        $this->assertSame(["AAG", "AATTCTT"], array_map(fn($oFragment) => $oFragment->getSequence()->getValue(), $aFragments));
        $this->assertTrue($aFragments[0]->getLeftEnd()->isBlunt(), "the sequence's own end");
        $this->assertSame("AATT", $aFragments[0]->getRightEnd()->getOverhangSequence());
        $this->assertSame("AATT", $aFragments[1]->getLeftEnd()->getOverhangSequence());
        $this->assertTrue($aFragments[1]->getRightEnd()->isBlunt());
    }

    public function testABluntAndAThreePrimeEnzyme()
    {
        $oSmaI = $this->manager->digest(new DnaSequence("AACCCGGGTT"), [$this->catalog->getByName("SmaI")]);
        $this->assertTrue($oSmaI->getCuts()[0]->getEnd()->isBlunt());
        $this->assertSame(["AACCC", "GGGTT"], array_map(fn($oFragment) => $oFragment->getSequence()->getValue(), $oSmaI->getFragments()));

        $oKpnI = $this->manager->digest(new DnaSequence("AAGGTACCTT"), [$this->catalog->getByName("KpnI")]);
        $this->assertSame(RestrictionEnd::THREE_PRIME, $oKpnI->getCuts()[0]->getEnd()->getType());
        $this->assertSame("GTAC", $oKpnI->getCuts()[0]->getEnd()->getOverhangSequence());
    }

    public function testASiteOnTheReverseStrandIsFoundToo()
    {
        // GGTACC read on the reverse strand is its own reverse complement : one site, cut once
        $oResult = $this->manager->digest(new DnaSequence("TTGGTACCAA"), [$this->catalog->getByName("KpnI")]);

        $this->assertCount(1, $oResult->getCuts());

        // AATTC is not palindromic : the reverse complement of GAATTC is GAATTC, but a site such as
        // BsaI's GGTCTC is found as GAGACC on the upper strand
        $oBsaI = $this->manager->digest(new DnaSequence("AAAAAAGAGACCAAAAAAAAAAAA"), [$this->catalog->getByName("BsaI")]);
        $this->assertCount(1, $oBsaI->getCuts());
        $this->assertTrue($oBsaI->getCuts()[0]->isReverseStrand());
    }

    /**
     * A Type IIs enzyme cuts away from its site : near an end, the cut falls off the sequence (BsaI
     * cuts 1 and 5 bases past GGTCTC, so the lower strand here would be cut at 11 in 11 bases).
     */
    public function testACutThatFallsOutsideTheSequenceIsIgnoredWithAWarning()
    {
        $oResult = $this->manager->digest(new DnaSequence("GGTCTCAAAAA"), [$this->catalog->getByName("BsaI")]);

        $this->assertSame([], $oResult->getCuts());
        $this->assertSame([], $oResult->getFragments());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString('"BsaI"', $oResult->getWarnings()[0]);
        $this->assertStringContainsString("outside the sequence", $oResult->getWarnings()[0]);
    }

    public function testSeveralEnzymesGiveTheFragmentsOfAllTheirCuts()
    {
        $oResult = $this->manager->digest(
            new DnaSequence("AAGAATTCCCGGGTT"),
            [$this->catalog->getByName("EcoRI"), $this->catalog->getByName("SmaI")]
        );

        $this->assertSame(["EcoRI", "SmaI"], $oResult->getEnzymeNames());
        $this->assertSame(
            ["AAG", "AATTCCC", "GGGTT"],
            array_map(fn($oFragment) => $oFragment->getSequence()->getValue(), $oResult->getFragments())
        );
    }

    public function testNoSiteNoFragment()
    {
        $oResult = $this->manager->digest(new DnaSequence("AAAAAAAAAA"), [$this->catalog->getByName("EcoRI")]);

        $this->assertSame([], $oResult->getCuts());
        $this->assertSame([], $oResult->getFragments());
        $this->assertSame([], $oResult->getWarnings());
    }

    public function testAnEnzymeLongerThanTheSequenceIsSkippedWithAWarning()
    {
        $oResult = $this->manager->digest(new DnaSequence("GAAT"), [$this->catalog->getByName("EcoRI")]);

        $this->assertSame([], $oResult->getCuts());
        $this->assertStringContainsString("exceeds sequence length", $oResult->getWarnings()[0]);
    }

    public function testOnlyEnzymeDefinitionsAreAccepted()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->manager->digest(new DnaSequence("GAATTC"), ["EcoRI"]);
    }
}
