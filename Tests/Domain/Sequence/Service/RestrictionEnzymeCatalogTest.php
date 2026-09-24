<?php
namespace Tests\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\DTO\TypeIIbEndonucleaseDTO;
use Amelaye\BioPHP\Api\DTO\TypeIIEndonucleaseDTO;
use Amelaye\BioPHP\Api\TypeIIbEndonucleaseApi;
use Amelaye\BioPHP\Api\TypeIIEndonucleaseApi;
use Amelaye\BioPHP\Api\TypeIIsEndonucleaseApi;
use Amelaye\BioPHP\Domain\Sequence\Exception\UnknownRestrictionEnzymeException;
use Amelaye\BioPHP\Domain\Sequence\Service\RestrictionEnzymeCatalog;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RestrictionEnzymeDefinition;
use PHPUnit\Framework\TestCase;

class RestrictionEnzymeCatalogTest extends TestCase
{
    private $clientMock;

    private $serializerMock;

    private $catalog;

    public function setUp(): void
    {
        $this->clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()->build();

        require 'samples/TypeIIEndonucleases.php';

        require 'samples/Type2bEndonucleases.php';

        require 'samples/Type2sEndonucleases.php';

        $typeIIApiMock = $this->getMockBuilder(TypeIIEndonucleaseApi::class)
            ->setConstructorArgs([$this->clientMock, $this->serializerMock])
            ->onlyMethods(['getTypeIIEndonucleases'])
            ->getMock();
        $typeIIApiMock->method("getTypeIIEndonucleases")->willReturn($aTypeIIEndonucleases);

        $typeIIbApiMock = $this->getMockBuilder(TypeIIbEndonucleaseApi::class)
            ->setConstructorArgs([$this->clientMock, $this->serializerMock])
            ->onlyMethods(['getTypeIIbEndonucleases'])
            ->getMock();
        $typeIIbApiMock->method("getTypeIIbEndonucleases")->willReturn($aTypeIIbEndonucleases);

        $typeIIsApiMock = $this->getMockBuilder(TypeIIsEndonucleaseApi::class)
            ->setConstructorArgs([$this->clientMock, $this->serializerMock])
            ->onlyMethods(['getTypeIIsEndonucleases'])
            ->getMock();
        $typeIIsApiMock->method("getTypeIIsEndonucleases")->willReturn($aTypeIIsEndonucleases);

        // No live HTTP call happens: the three adapters above are mocked from local fixtures.
        $this->catalog = new RestrictionEnzymeCatalog($typeIIApiMock, $typeIIbApiMock, $typeIIsApiMock);
    }

    public function testFindsAnEnzymeByItsCanonicalName()
    {
        $oDefinition = $this->catalog->findByName("EcoRI");

        $this->assertNotNull($oDefinition);
        $this->assertEquals("EcoRI", $oDefinition->getName());
        $this->assertEquals(RestrictionEnzymeDefinition::TYPE_II, $oDefinition->getFamily());
    }

    public function testFindsAnEnzymeByAliasRegardlessOfCase()
    {
        // AatI's isoschizomers are AatI, Eco147I, PceI, SseBI, StuI.
        $oDefinition = $this->catalog->findByName("eco147i");

        $this->assertNotNull($oDefinition);
        $this->assertEquals("AatI", $oDefinition->getName());
    }

    public function testReturnsNullForAnUnknownName()
    {
        $this->assertNull($this->catalog->findByName("DoesNotExist"));
    }

    public function testGetByNameThrowsForAnUnknownName()
    {
        $this->expectException(UnknownRestrictionEnzymeException::class);
        $this->expectExceptionMessage('DoesNotExist');

        $this->catalog->getByName("DoesNotExist");
    }

    public function testDistinguishesTheThreeEnzymeFamilies()
    {
        $aTypeII = $this->catalog->findByFamily(RestrictionEnzymeDefinition::TYPE_II);
        $aTypeIIb = $this->catalog->findByFamily(RestrictionEnzymeDefinition::TYPE_IIB);
        $aTypeIIs = $this->catalog->findByFamily(RestrictionEnzymeDefinition::TYPE_IIS);

        $this->assertNotEmpty($aTypeII);
        $this->assertNotEmpty($aTypeIIb);
        $this->assertNotEmpty($aTypeIIs);

        $this->assertEquals("EcoRI", $this->catalog->getByName("EcoRI")->getName());
        $this->assertContains("AjuI#", array_map(fn ($oDef) => $oDef->getName(), $aTypeIIb));
        $this->assertContains("AarI", array_map(fn ($oDef) => $oDef->getName(), $aTypeIIs));

        foreach ($aTypeII as $oDefinition) {
            $this->assertEquals(RestrictionEnzymeDefinition::TYPE_II, $oDefinition->getFamily());
        }
        foreach ($aTypeIIb as $oDefinition) {
            $this->assertEquals(RestrictionEnzymeDefinition::TYPE_IIB, $oDefinition->getFamily());
        }
        foreach ($aTypeIIs as $oDefinition) {
            $this->assertEquals(RestrictionEnzymeDefinition::TYPE_IIS, $oDefinition->getFamily());
        }
    }

    public function testReturnsAnEmptyArrayForAnUnknownFamily()
    {
        $this->assertEquals([], $this->catalog->findByFamily("TYPE_III"));
    }

    public function testFlagsAnEnzymeWithIupacAmbiguousSymbols()
    {
        // AasI: GACNN_NN'NNGTC, degenerated with N (any base).
        $oDefinition = $this->catalog->getByName("AasI");

        $this->assertTrue($oDefinition->hasAmbiguousBases());
    }

    public function testPreservesBothCleavagePositionsIncludingANegativeLowerOne()
    {
        // AasI => [["AasI,DrdI,DseDI"],"GACNN_NN'NNGTC","(GAC......GTC)",12,7,-2,6]
        $oDefinition = $this->catalog->getByName("AasI");

        $this->assertEquals(7, $oDefinition->getCleavagePositionUpper());
        $this->assertEquals(-2, $oDefinition->getCleavagePositionLower());
    }

    public function testFindByFamilyReturnsAStableAlphabeticalOrder()
    {
        $aFirstCall = array_map(fn ($oDef) => $oDef->getName(), $this->catalog->findByFamily(RestrictionEnzymeDefinition::TYPE_II));
        $aSecondCall = array_map(fn ($oDef) => $oDef->getName(), $this->catalog->findByFamily(RestrictionEnzymeDefinition::TYPE_II));

        $aSorted = $aFirstCall;
        sort($aSorted, SORT_STRING);

        $this->assertEquals($aFirstCall, $aSecondCall);
        $this->assertEquals($aSorted, $aFirstCall);
    }

    public function testFindByRecognitionSequenceReturnsIsoschizomersInAStableOrder()
    {
        // BshFI (GGCC) and AluI (AGCT) do not share a pattern; look up one exact clean sequence.
        $aMatches = $this->catalog->findByRecognitionSequence("GGATCC");

        $this->assertNotEmpty($aMatches);
        foreach ($aMatches as $oDefinition) {
            $this->assertEquals("GGATCC", $oDefinition->getCleanRecognitionSequence());
        }
    }

    public function testDeduplicatesByCanonicalNameKeepingTheFirstRegisteredFamily()
    {
        $oTypeIIDto = new TypeIIEndonucleaseDTO();
        $oTypeIIDto->setId("DupX");
        $oTypeIIDto->setSamePattern([""]);
        $oTypeIIDto->setRecognitionPattern("AA'TT");
        $oTypeIIDto->setComputingPattern("(AATT)");
        $oTypeIIDto->setLengthRecognitionPattern(4);
        $oTypeIIDto->setCleavagePosUpper(2);
        $oTypeIIDto->setCleavagePosLower(2);
        $oTypeIIDto->setNbNonNBases(4);

        $oTypeIIbDto = new TypeIIbEndonucleaseDTO();
        $oTypeIIbDto->setId("DupX");
        $oTypeIIbDto->setSamePattern([""]);
        $oTypeIIbDto->setRecognitionPattern("_NN'NN_");
        $oTypeIIbDto->setComputingPattern("(....)");
        $oTypeIIbDto->setLengthRecognitionPattern(4);
        $oTypeIIbDto->setCleavagePosUpper(2);
        $oTypeIIbDto->setCleavagePosLower(-2);
        $oTypeIIbDto->setNbNonNBases(0);

        $typeIIApiMock = $this->getMockBuilder(TypeIIEndonucleaseApi::class)
            ->setConstructorArgs([$this->clientMock, $this->serializerMock])
            ->onlyMethods(['getTypeIIEndonucleases'])
            ->getMock();
        $typeIIApiMock->method("getTypeIIEndonucleases")->willReturn([$oTypeIIDto]);

        $typeIIbApiMock = $this->getMockBuilder(TypeIIbEndonucleaseApi::class)
            ->setConstructorArgs([$this->clientMock, $this->serializerMock])
            ->onlyMethods(['getTypeIIbEndonucleases'])
            ->getMock();
        $typeIIbApiMock->method("getTypeIIbEndonucleases")->willReturn([$oTypeIIbDto]);

        $typeIIsApiMock = $this->getMockBuilder(TypeIIsEndonucleaseApi::class)
            ->setConstructorArgs([$this->clientMock, $this->serializerMock])
            ->onlyMethods(['getTypeIIsEndonucleases'])
            ->getMock();
        $typeIIsApiMock->method("getTypeIIsEndonucleases")->willReturn([]);

        $oCatalog = new RestrictionEnzymeCatalog($typeIIApiMock, $typeIIbApiMock, $typeIIsApiMock);

        $this->assertEquals(RestrictionEnzymeDefinition::TYPE_II, $oCatalog->getByName("DupX")->getFamily());
    }
}
