<?php
namespace Tests\Api\DTO;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Amelaye\BioPHP\Api\DTO\ProteinReductionDTO;

class ProteinReductionDTOTest extends WebTestCase
{
    public function testNewProteinReductionPDO()
    {
        $reduction = new ProteinReductionDTO();
        $reduction->setId("1");
        $reduction->setAlphabet("Murphy10");
        $reduction->setLetters("LCAGSPFEKH");
        $reduction->setPattern("L|V|I|M");
        $reduction->setNature("L: Large hydrophobic");
        $reduction->setReduction("l");
        $reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");

        $this->assertEquals("1", $reduction->getId());
        $this->assertEquals("Murphy10", $reduction->getAlphabet());
        $this->assertEquals("LCAGSPFEKH", $reduction->getLetters());
        $this->assertEquals("L|V|I|M", $reduction->getPattern());
        $this->assertEquals("L: Large hydrophobic", $reduction->getNature());
        $this->assertEquals("l", $reduction->getReduction());
        $this->assertEquals("Murphy et al, 2000; 10 letters alphabet", $reduction->getDescription());
    }
}