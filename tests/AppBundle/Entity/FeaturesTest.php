<?php


namespace Tests\AppBundle\Entity;


use AppBundle\Entity\Sequencing\Features;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeaturesTest extends WebTestCase
{
    public function testNewFeature()
    {
        $oFeature = new Features();
        $oFeature->setPrimAcc("NM_031438");
        $oFeature->setFtKey("gene");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(3488);
        $oFeature->setFtQual("gene");
        $oFeature->setFtValue("NUDT12");

        $this->assertEquals("NM_031438", $oFeature->getPrimAcc("NM_031438"));
        $this->assertEquals("gene", $oFeature->getFtKey("gene"));
        $this->assertEquals(1, $oFeature->getFtFrom());
        $this->assertEquals(3488, $oFeature->getFtTo());
        $this->assertEquals("gene", $oFeature->getFtQual());
        $this->assertEquals("NUDT12", $oFeature->getFtValue());
    }
}