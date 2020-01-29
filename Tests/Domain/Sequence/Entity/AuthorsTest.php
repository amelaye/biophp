<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Sequencing\Author;
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
}