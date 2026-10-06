<?php
namespace Tests\Domain\Variants\Service;

use Amelaye\BioPHP\Domain\Variants\Entity\VcfVariantRecord;
use Amelaye\BioPHP\Domain\Variants\Exception\InvalidVcfRecordException;
use Amelaye\BioPHP\Domain\Variants\Service\VcfVariantRecordMapper;
use Amelaye\BioPHP\Domain\Variants\ValueObject\VcfVariant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Domain\SqliteEntityManagerTrait;

class VcfVariantRecordMapperTest extends TestCase
{
    use SqliteEntityManagerTrait;

    public function testToRecordCopiesEveryField()
    {
        $oVariant = new VcfVariant("chr1", 12345, "rs99", "A", ["G", "T"], 50.5, "PASS", ["DP" => "14", "DB" => true]);

        $oRecord = (new VcfVariantRecordMapper())->toRecord($oVariant);

        $this->assertNull($oRecord->getId());
        $this->assertSame("chr1", $oRecord->getChrom());
        $this->assertSame(12345, $oRecord->getPosition());
        $this->assertSame("rs99", $oRecord->getVariantId());
        $this->assertSame("A", $oRecord->getReference());
        $this->assertSame(["G", "T"], $oRecord->getAlternates());
        $this->assertSame(50.5, $oRecord->getQuality());
        $this->assertSame("PASS", $oRecord->getFilter());
        $this->assertSame(["DP" => "14", "DB" => true], $oRecord->getInfo());
    }

    #[DataProvider("variantProvider")]
    public function testVariantSurvivesADatabaseRoundTrip(VcfVariant $oOriginal)
    {
        $oEm = $this->createEntityManagerWithSchema();
        $oMapper = new VcfVariantRecordMapper();

        $oRecord = $oMapper->toRecord($oOriginal);
        $oEm->persist($oRecord);
        $oEm->flush();
        $oEm->clear();

        $oRestored = $oMapper->toVariant($oEm->find(VcfVariantRecord::class, $oRecord->getId()));

        $this->assertEquals($oOriginal, $oRestored);
        $this->assertSame($oOriginal->isPass(), $oRestored->isPass());
    }

    public static function variantProvider(): array
    {
        return [
            "snv with flag and value info" => [new VcfVariant("chr1", 100, "rs1", "A", ["G"], 99.0, "PASS", ["DP" => "14", "DB" => true])],
            "no ALT, all optional columns missing" => [new VcfVariant("chrM", 1, null, "T", [], null, null)],
            "symbolic structural allele" => [new VcfVariant("2", 5000, null, "N", ["<DEL>"], null, "q10")],
            "breakend notation" => [new VcfVariant("2", 321682, "bnd_V", "T", ["]13:123456]T"], 6.0, "PASS")],
        ];
    }

    public function testToVariantRevalidatesStoredData()
    {
        $oRecord = new VcfVariantRecord();
        $oRecord->setChrom("chr1")->setPosition(0)->setReference("A");

        $this->expectException(InvalidVcfRecordException::class);
        (new VcfVariantRecordMapper())->toVariant($oRecord);
    }
}
