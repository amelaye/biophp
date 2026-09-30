<?php
namespace Tests\Domain\Tools\Service;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\ElementApi;
use Amelaye\BioPHP\Api\NucleotidApi;
use Amelaye\BioPHP\Domain\Sequence\Builder\SequenceBuilder;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;
use Amelaye\BioPHP\Domain\Tools\Service\CodonOptimizer;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CodonUsageTable;
use PHPUnit\Framework\TestCase;

class CodonOptimizerTest extends TestCase
{
    private $optimizer;

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

        $this->optimizer = new CodonOptimizer($sequenceBuilder);
    }

    /**
     * Met has only one codon (ATG), trivially "best". Phe's highest-usage synonym is TTT (30 > 10),
     * Tyr's is TAC (20 > 5) - both real, textbook standard-genetic-code facts.
     */
    public function testPicksTheHighestUsageSynonymousCodonForEachResidue()
    {
        $oTable = new CodonUsageTable([
            "TTT" => 30, "TTC" => 10,
            "TAT" => 5, "TAC" => 20,
        ]);

        $oDna = $this->optimizer->optimize(new AminoAcidSequence("MFY"), $oTable);

        $this->assertEquals("ATGTTTTAC", $oDna->getValue());
    }

    /**
     * With no reference data at all for either Phe synonym (both counts 0, a tie), the codon
     * enumeration order (bases scanned A,C,G,T at each position) reaches "TTC" before "TTT" -
     * TTA and TTG translate to Leu and are skipped first, so TTC is the first Phe-translating codon
     * encountered, and wins the tie deterministically.
     */
    public function testATiedZeroUsageResidueResolvesToTheFirstCodonInEnumerationOrder()
    {
        $oDna = $this->optimizer->optimize(new AminoAcidSequence("F"), new CodonUsageTable([]));

        $this->assertEquals("TTC", $oDna->getValue());
    }

    public function testOptimizesAProteinIncludingATrailingStop()
    {
        $oTable = new CodonUsageTable(["TAA" => 5, "TAG" => 1, "TGA" => 1]);

        $oDna = $this->optimizer->optimize(new AminoAcidSequence("M*"), $oTable);

        $this->assertEquals("ATGTAA", $oDna->getValue());
    }
}
