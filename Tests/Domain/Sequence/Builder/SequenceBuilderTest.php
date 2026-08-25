<?php
namespace Tests\Domain\Sequence\Builder;

use Amelaye\BioPHP\Domain\Sequence\Builder\SequenceBuilder;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\InvalidSequenceException;
use PHPUnit\Framework\TestCase;

class SequenceBuilderTest extends TestCase
{
    /**
     * @param  string   $sMolType
     * @param  string   $sSequence
     * @return SequenceBuilder
     */
    private function makeBuilder($sMolType, $sSequence)
    {
        $oSequence = new Sequence();
        $oSequence->setMolType($sMolType);
        $oSequence->setSequence($sSequence);

        $oBuilder = new SequenceBuilder($this->createMock(SequenceManager::class));
        $oBuilder->setSequence($oSequence);

        return $oBuilder;
    }

    public function testGetMolecularSequenceWrapsTheInjectedRecord()
    {
        $oBuilder = $this->makeBuilder("ss-DNA", "atgcgt");

        $oValue = $oBuilder->getMolecularSequence();

        $this->assertInstanceOf(DnaSequence::class, $oValue);
        $this->assertEquals("ATGCGT", $oValue->getValue());
        $this->assertEquals("ACGCAT", $oValue->reverseComplement()->getValue());
    }

    public function testGetMolecularSequenceLeavesTheInjectedRecordUntouched()
    {
        $oBuilder = $this->makeBuilder("DNA", "atgcgt");
        $oBuilder->getMolecularSequence();

        $this->assertEquals("atgcgt", $oBuilder->getSequence()->getSequence());
    }

    public function testGetMolecularSequenceRejectsAnInconsistentRecord()
    {
        $oBuilder = $this->makeBuilder("DNA", "ATGCJ");

        $this->expectException(InvalidSequenceException::class);

        $oBuilder->getMolecularSequence();
    }
}
