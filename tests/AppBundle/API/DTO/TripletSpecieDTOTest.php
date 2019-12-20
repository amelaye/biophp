<?php


namespace Tests\AppBundle\API\DTO;
use AppBundle\Api\DTO\TripletSpecieDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class TripletSpecieDTOTest extends WebTestCase
{
    public function testNewTripletSpecieDTO()
    {
        $triplets_chlorophycean_mitochondrial = [
            '(TTT |TTC )',
            '(TTA |TTG |CT. |TAG )',
            '(ATT |ATC |ATA )',
            '(ATG )',
            '(GT. )',
            '(TC. |AGT |AGC )',
            '(CC. )',
            '(AC. )',
            '(GC. )',
            '(TAT |TAC )',
            '(TAA |TGA )',
            '(CAT |CAC )',
            '(CAA |CAG )',
            '(AAT |AAC )',
            '(AAA |AAG )',
            '(GAT |GAC )',
            '(GAA |GAG )',
            '(TGT |TGC )',
            '(TGG )',
            '(CG. |AGA |AGG )',
            '(GG. )',
            '(\S\S\S )'
        ];

        $triplet = new TripletSpecieDTO();
        $triplet->setId("1");
        $triplet->setNature("chlorophycean mitochondrial");
        $triplet->setTripletsGroups($triplets_chlorophycean_mitochondrial);
        $triplet->setTriplets(["TTY","YWN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY","TRA",
            "CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN","GGN","NNN"]);

        $this->assertEquals("1", $triplet->getId());
        $this->assertEquals("chlorophycean mitochondrial", $triplet->getNature());
        $this->assertEquals($triplets_chlorophycean_mitochondrial, $triplet->getTripletsGroups());
        $this->assertEquals(["TTY","YWN","ATH","ATG","GTN","WSN","CCN","ACN","GCN","TAY","TRA",
            "CAY","CAR","AAY","AAR","GAY","GAR","TGY","TGG","MGN","GGN","NNN"], $triplet->getTriplets());
    }
}