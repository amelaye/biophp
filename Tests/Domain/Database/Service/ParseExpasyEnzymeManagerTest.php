<?php
namespace Tests\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseExpasyEnzymeManagerTest extends TestCase
{
    public function testFetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("expasydb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("1.1.1.2");
        $collectionElement->setFileName("sample.expasy");
        $collectionElement->setDbFormat("EXPASY_ENZYME");
        $collectionElement->setSeqCount(1);
        $collectionElement->setLineNo(0);
        $collectionElement->setCollection($collection);

        $mockedEm = $this->createMock(EntityManager::class);

        $repo = $this->createMock(EntityRepository::class);
        $mockedEm->expects($this->once())
            ->method('getRepository')
            ->with(CollectionElement::class)
            ->willReturn($repo);
        $repo->expects($this->once())->method('findOneBy')
            ->with(['idElement' => "1.1.1.2"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");
        $oParseExpasyEnzymeManager = $databaseManager->fetch("1.1.1.2");

        $this->assertEquals("1.1.1.2", $oParseExpasyEnzymeManager->getId());
        $this->assertEquals("Alcohol dehydrogenase (NADP+).", $oParseExpasyEnzymeManager->getDescription());
        $this->assertEquals(["Aldehyde reductase (NADPH)"], $oParseExpasyEnzymeManager->getAlternateNames());
        $this->assertEquals("An alcohol + NADP(+) = an aldehyde + NADPH", $oParseExpasyEnzymeManager->getCatalyticActivity());
        $this->assertEquals(["Zinc"], $oParseExpasyEnzymeManager->getCofactors());
        $this->assertEquals(
            "-!- Some members of this group oxidize only primary alcohols; others act\n"
            . "    also on secondary alcohols.\n"
            . "-!- May be identical with EC 1.1.1.19, EC 1.1.1.33 and EC 1.1.1.55.\n"
            . "-!- A-specific with respect to NADPH.",
            $oParseExpasyEnzymeManager->getComments()
        );

        $aDiseases = $oParseExpasyEnzymeManager->getDiseases();
        $this->assertCount(1, $aDiseases);
        $this->assertEquals("6-phosphogluconate dehydrogenase deficiency", $aDiseases[0]->getDisease());
        $this->assertEquals("172200", $aDiseases[0]->getReference());

        $this->assertEquals(["PDOC00061"], $oParseExpasyEnzymeManager->getPrositeRefs());
        $this->assertEquals(
            ["P35630" => "ADH1_ENTHI", "Q24857" => "ADH3_ENTHI", "O57380" => "ADH4_RANPE"],
            $oParseExpasyEnzymeManager->getSwissprotRefs()
        );
    }
}
