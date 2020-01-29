<?php
/**
 * Protein Entity Testing
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 16 february 2019
 * Last modified 16 february 2019
 */
namespace Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use AppBundle\Entity\Protein;

class ProteinTest extends TestCase
{
    /**
     * Tests for Protein Entity
     */
    public function testNewProtein()
    {
        $oProtein = new Protein();
        $oProtein->setId("123");
        $oProtein->setSequence(array("test1", "test2"));
        $oProtein->setName("Ma Proteine");

        $sIdProtein = $oProtein->getId();
        $aSequence = $oProtein->getSequence();
        $sNameProtein = $oProtein->getName();

        $this->assertEquals("123", $sIdProtein);
        $this->assertEquals(array("test1", "test2"), $aSequence);
        $this->assertEquals("Ma Proteine", $sNameProtein);
    }
}