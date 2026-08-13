<?php
namespace Tests\Domain\Model;

use Amelaye\BioPHP\Domain\Model\PrositeDbRef;
use PHPUnit\Framework\TestCase;

class PrositeDbRefTest extends TestCase
{
    public function testNewPrositeDbRef()
    {
        $oDbRef = new PrositeDbRef();
        $oDbRef->setAccession("P00001");
        $oDbRef->setEntryName("TEST1_HUMAN");
        $oDbRef->setTruePositive(true);

        $this->assertEquals("P00001", $oDbRef->getAccession());
        $this->assertEquals("TEST1_HUMAN", $oDbRef->getEntryName());
        $this->assertTrue($oDbRef->isTruePositive());
    }

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oDbRef = new PrositeDbRef();

        $this->assertSame("", $oDbRef->getAccession());
        $this->assertSame("", $oDbRef->getEntryName());
        $this->assertFalse($oDbRef->isTruePositive());
    }
}
