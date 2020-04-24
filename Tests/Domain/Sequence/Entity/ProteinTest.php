<?php
/**
 * Protein Entity Testing
 * @author Amélie DUVERNET aka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 16 february 2019
 * Last modified 16 february 2019
 */
namespace Tests\Domain\Sequence\Entity;

use PHPUnit\Framework\TestCase;
use Amelaye\BioPHP\Domain\Sequence\Entity\Protein;

class ProteinTest extends TestCase
{
    /**
     * Tests for Protein Entity
     */
    public function testNewProtein()
    {
        $oProtein = new Protein();
        $oProtein->setId("123");
        $oProtein->setSequence("test1");
        $oProtein->setName("Ma Proteine");

        $sIdProtein = $oProtein->getId();
        $aSequence = $oProtein->getSequence();
        $sNameProtein = $oProtein->getName();

        $this->assertEquals("123", $sIdProtein);
        $this->assertEquals("test1", $aSequence);
        $this->assertEquals("Ma Proteine", $sNameProtein);
    }
}