<?php
namespace Tests\Domain\Cloning\Service\Mapper;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\Entity\PlasmidFeatureRecord;
use Amelaye\BioPHP\Domain\Cloning\Entity\PlasmidRecord;
use Amelaye\BioPHP\Domain\Cloning\Service\Mapper\PlasmidRecordMapper;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;
use PHPUnit\Framework\TestCase;
use Tests\Domain\SqliteEntityManagerTrait;

class PlasmidRecordMapperTest extends TestCase
{
    use SqliteEntityManagerTrait;

    private function buildPlasmid(): Plasmid
    {
        return new Plasmid(
            "pTest",
            new CircularDnaSequence("ATGCATGCATGCATGCATGC"),
            [
                new PlasmidFeature("lacZ", FeatureType::CDS, 3, 9, Strand::REVERSE, "#FF00AA", "a note", "ext-1", ["gb_key" => "gene", "tags" => ["a", "b"], "score" => 1.5, "flag" => true]),
                new PlasmidFeature("ori", FeatureType::ORIGIN_OF_REPLICATION, 18, 4),
                new PlasmidFeature("AmpR", FeatureType::MARKER, 10, 14, Strand::FORWARD),
            ],
            "A test plasmid",
            "acc-42",
            ["source" => "unit test", "nested" => ["n" => 1, "z" => null]]
        );
    }

    public function testToRecordCopiesEveryField()
    {
        $oRecord = (new PlasmidRecordMapper())->toRecord($this->buildPlasmid());

        $this->assertNull($oRecord->getId());
        $this->assertSame("pTest", $oRecord->getName());
        $this->assertSame("ATGCATGCATGCATGCATGC", $oRecord->getSequence());
        $this->assertSame("A test plasmid", $oRecord->getDescription());
        $this->assertSame("acc-42", $oRecord->getExternalId());
        $this->assertCount(3, $oRecord->getFeatures());

        $oFirst = $oRecord->getFeatures()->first();
        $this->assertSame(0, $oFirst->getPosition());
        $this->assertSame("lacZ", $oFirst->getName());
        $this->assertSame(3, $oFirst->getStartPosition());
        $this->assertSame(9, $oFirst->getEndPosition());
        $this->assertSame($oRecord, $oFirst->getPlasmid());
    }

    public function testToPlasmidRestoresFeatureOrderFromPosition()
    {
        $oRecord = new PlasmidRecord();
        $oRecord->setName("pShuffled")->setSequence("ATGCATGCAT");

        foreach ([[2, "third"], [0, "first"], [1, "second"]] as [$iPosition, $sName]) {
            $oFeature = new PlasmidFeatureRecord();
            $oFeature->setPosition($iPosition)->setName($sName)->setType(FeatureType::MISC_FEATURE)
                ->setStartPosition(1)->setEndPosition(2)->setStrand(Strand::NONE);
            $oRecord->addFeature($oFeature);
        }

        $aNames = array_map(function (PlasmidFeature $o) {
            return $o->getName();
        }, (new PlasmidRecordMapper())->toPlasmid($oRecord)->getFeatures());

        $this->assertSame(["first", "second", "third"], $aNames);
    }

    public function testToPlasmidRevalidatesStoredData()
    {
        $oRecord = new PlasmidRecord();
        $oRecord->setName("pBad")->setSequence("ATGCATGCAT");
        $oFeature = new PlasmidFeatureRecord();
        $oFeature->setName("x")->setType("NOT_A_TYPE")->setStartPosition(1)->setEndPosition(2)
            ->setStrand(Strand::NONE);
        $oRecord->addFeature($oFeature);

        $this->expectException(\InvalidArgumentException::class);
        (new PlasmidRecordMapper())->toPlasmid($oRecord);
    }

    public function testPlasmidSurvivesADatabaseRoundTrip()
    {
        $oEm = $this->createEntityManagerWithSchema();
        $oMapper = new PlasmidRecordMapper();
        $oOriginal = $this->buildPlasmid();

        $oRecord = $oMapper->toRecord($oOriginal);
        $oEm->persist($oRecord);
        $oEm->flush();
        $iId = $oRecord->getId();
        $this->assertNotNull($iId);
        $oEm->clear();

        $oLoaded = $oEm->find(PlasmidRecord::class, $iId);
        $oRestored = $oMapper->toPlasmid($oLoaded);

        $this->assertSame($oOriginal->getName(), $oRestored->getName());
        $this->assertTrue($oOriginal->getSequence()->equals($oRestored->getSequence()));
        $this->assertSame($oOriginal->getDescription(), $oRestored->getDescription());
        $this->assertSame($oOriginal->getExternalId(), $oRestored->getExternalId());
        $this->assertSame($oOriginal->getMetadata(), $oRestored->getMetadata());
        $this->assertEquals($oOriginal->getFeatures(), $oRestored->getFeatures());
        $this->assertTrue($oRestored->getFeatures()[1]->crossesOrigin());
    }

    public function testNullableFieldsAndEmptyMetadataRoundTrip()
    {
        $oEm = $this->createEntityManagerWithSchema();
        $oMapper = new PlasmidRecordMapper();
        $oOriginal = new Plasmid("bare", new CircularDnaSequence("ATGC"));

        $oRecord = $oMapper->toRecord($oOriginal);
        $oEm->persist($oRecord);
        $oEm->flush();
        $oEm->clear();

        $oRestored = $oMapper->toPlasmid($oEm->find(PlasmidRecord::class, $oRecord->getId()));

        $this->assertNull($oRestored->getDescription());
        $this->assertNull($oRestored->getExternalId());
        $this->assertSame([], $oRestored->getMetadata());
        $this->assertSame([], $oRestored->getFeatures());
    }

    public function testRemovingAPlasmidRemovesItsFeatures()
    {
        $oEm = $this->createEntityManagerWithSchema();
        $oRecord = (new PlasmidRecordMapper())->toRecord($this->buildPlasmid());
        $oEm->persist($oRecord);
        $oEm->flush();

        $oEm->remove($oRecord);
        $oEm->flush();

        $this->assertCount(0, $oEm->getRepository(PlasmidFeatureRecord::class)->findAll());
    }
}
