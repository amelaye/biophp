<?php
namespace Tests\Domain;

use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

class SchemaGenerationTest extends TestCase
{
    use SqliteEntityManagerTrait;

    private const EXPECTED_TABLES = [
        "accession", "author", "collection", "collection_element", "feature", "gb_sequence",
        "keyword", "parsed_record", "reference", "sequence", "sp_databank", "src_form",
    ];

    public function testEveryEntityProducesATable()
    {
        $oEm = $this->createEntityManager();
        $aMetadata = $oEm->getMetadataFactory()->getAllMetadata();
        $this->assertCount(count(self::EXPECTED_TABLES), $aMetadata);

        $sSql = implode("\n", (new SchemaTool($oEm))->getCreateSchemaSql($aMetadata));
        foreach (self::EXPECTED_TABLES as $sTable) {
            $this->assertMatchesRegularExpression("/CREATE TABLE \"?" . $sTable . "\"? /", $sSql);
        }
    }

    public function testEveryForeignKeyTargetsAUniqueKey()
    {
        $oEm = $this->createEntityManager();
        $oSchema = (new SchemaTool($oEm))->getSchemaFromMetadata($oEm->getMetadataFactory()->getAllMetadata());

        $iChecked = 0;
        foreach ($oSchema->getTables() as $oTable) {
            foreach ($oTable->getForeignKeys() as $oForeignKey) {
                $oTarget = $oSchema->getTable($oForeignKey->getForeignTableName());
                $aReferenced = $oForeignKey->getForeignColumns();
                sort($aReferenced);

                $aUniqueKeys = [$oTarget->getPrimaryKey()->getColumns()];
                foreach ($oTarget->getIndexes() as $oIndex) {
                    if ($oIndex->isUnique()) {
                        $aUniqueKeys[] = $oIndex->getColumns();
                    }
                }
                $aUniqueKeys = array_map(function ($aColumns) {
                    sort($aColumns);
                    return $aColumns;
                }, $aUniqueKeys);

                $this->assertContains(
                    $aReferenced,
                    $aUniqueKeys,
                    $oTable->getName() . " has a foreign key on " . $oTarget->getName()
                    . "(" . implode(", ", $aReferenced) . ") which is not a primary or unique key."
                );
                $iChecked++;
            }
        }
        $this->assertGreaterThan(0, $iChecked);
    }

    public function testPrimaryAccessionHasNoPhantomDefault()
    {
        $oEm = $this->createEntityManager();
        $sSql = implode("\n", (new SchemaTool($oEm))->getCreateSchemaSql($oEm->getMetadataFactory()->getAllMetadata()));

        $this->assertDoesNotMatchRegularExpression("/prim_acc VARCHAR\(8\) DEFAULT/", $sSql);
    }

    public function testOrganismSurvivesAPersistenceRoundTrip()
    {
        $oEm = $this->createEntityManagerWithSchema();

        $aOrganism = ["Homo sapiens", "Eukaryota", "Metazoa", "Chordata"];
        $oSequence = new Sequence();
        $oSequence->setPrimAcc("A00001");
        $oSequence->setEntryName("TEST");
        $oSequence->setSequence("ATGC");
        $oSequence->setOrganism($aOrganism);
        $oEm->persist($oSequence);
        $oEm->flush();
        $oEm->clear();

        $oFound = $oEm->find(Sequence::class, "A00001");
        $this->assertSame($aOrganism, $oFound->getOrganism());
    }
}
