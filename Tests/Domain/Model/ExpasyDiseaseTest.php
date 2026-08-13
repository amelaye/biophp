<?php
namespace Tests\Domain\Model;

use Amelaye\BioPHP\Domain\Model\ExpasyDisease;
use PHPUnit\Framework\TestCase;

class ExpasyDiseaseTest extends TestCase
{
    public function testNewExpasyDisease()
    {
        $oDisease = new ExpasyDisease();
        $oDisease->setDisease("6-phosphogluconate dehydrogenase deficiency");
        $oDisease->setReference("172200");

        $this->assertEquals("6-phosphogluconate dehydrogenase deficiency", $oDisease->getDisease());
        $this->assertEquals("172200", $oDisease->getReference());
    }

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oDisease = new ExpasyDisease();

        $this->assertSame("", $oDisease->getDisease());
        $this->assertSame("", $oDisease->getReference());
    }
}
