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

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oKeywords = new Keyword();

        $this->assertSame("", $oKeywords->getPrimAcc());
        $this->assertSame("", $oKeywords->getKeywords());
    }
}