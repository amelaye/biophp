<?php
namespace Tests\Domain\Sequence\Entity;

use Amelaye\BioPHP\Domain\Sequence\Entity\Keyword;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class KeywordsTest extends WebTestCase
{
    public function testNewKeyword()
    {
        $oKeywords = new Keyword();
        $oKeywords->setPrimAcc("NM_031438");
        $oKeywords->setKeywords("RefSeq.");

        $this->assertEquals("NM_031438", $oKeywords->getPrimAcc());
        $this->assertEquals("RefSeq.", $oKeywords->getKeywords());
    }
}