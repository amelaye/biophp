<?php
namespace Tests\Domain\Sequence\Entity;

use Amelaye\BioPHP\Domain\Sequence\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthorsTest extends WebTestCase
{
    public function testNewAuthor()
    {
        $oAuthor = new Author();
        $oAuthor->setPrimAcc("NM_031438");
        $oAuthor->setRefno("1");
        $oAuthor->setAuthor("Sahni N");

        $this->assertEquals("NM_031438", $oAuthor->getPrimAcc());
        $this->assertEquals("1", $oAuthor->getRefno());
        $this->assertEquals("Sahni N", $oAuthor->getAuthor());
    }

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oAuthor = new Author();

        $this->assertSame("", $oAuthor->getPrimAcc());
        $this->assertSame(0, $oAuthor->getRefno());
        $this->assertSame("", $oAuthor->getAuthor());
    }
}