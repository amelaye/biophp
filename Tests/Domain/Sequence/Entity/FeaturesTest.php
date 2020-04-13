<?php


namespace Tests\AppBundle\Entity;


use Amelaye\BioPHP\Domain\Sequence\Entity\Feature;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeaturesTest extends WebTestCase
{
    public function testNewFeature()
    {
        $oFeature = new Feature();
        $oFeature->setPrimAcc("NM_031438");
        $oFeature->setFtKey("gene");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(3488);
        $oFeature->setFtQual("gene");
        $oFeature->setFtValue("NUDT12");
        $oFeature->setFtDesc("description of my feature.");

        $this->assertEquals("NM_031438", $oFeature->getPrimAcc());
        $this->assertEquals("gene", $oFeature->getFtKey());
        $this->assertEquals(1, $oFeature->getFtFrom());
        $this->assertEquals(3488, $oFeature->getFtTo());
        $this->assertEquals("gene", $oFeature->getFtQual());
        $this->assertEquals("NUDT12", $oFeature->getFtValue());
        $this->assertEquals("description of my feature.", $oFeature->getFtDesc());
    }
}