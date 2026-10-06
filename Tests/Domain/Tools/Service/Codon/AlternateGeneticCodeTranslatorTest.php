<?php
namespace Tests\Domain\Tools\Service\Codon;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\ElementApi;
use Amelaye\BioPHP\Api\NucleotidApi;
use Amelaye\BioPHP\Domain\Sequence\Builder\SequenceBuilder;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use Amelaye\BioPHP\Domain\Tools\Service\Codon\AlternateGeneticCodeTranslator;
use Amelaye\BioPHP\Domain\Tools\ValueObject\GeneticCodeTable;
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
}
