<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Sequencing\Keywords;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class KeywordsTest extends WebTestCase
{
    public function testNewKeyword()
    {
        $oKeywords = new Keywords();
        $oKeywords->setPrimAcc("NM_031438");
        $oKeywords->setKeywords("RefSeq.");

        $this->assertEquals("NM_031438", $oKeywords->getPrimAcc());
        $this->assertEquals("RefSeq.", $oKeywords->getKeywords());
    }
}