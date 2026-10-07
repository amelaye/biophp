<?php
namespace Tests\Domain\Sequence\Entity;

use Amelaye\BioPHP\Domain\Parser\Service\ParseGenbankManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseSwissprotManager;
use Amelaye\BioPHP\Domain\Sequence\Entity\Author;
use Amelaye\BioPHP\Domain\Sequence\Entity\Feature;
use Amelaye\BioPHP\Domain\Sequence\Entity\GbSequence;
use Amelaye\BioPHP\Domain\Sequence\Entity\Reference;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Amelaye\BioPHP\Domain\Sequence\Entity\SpDatabank;
use PHPUnit\Framework\TestCase;
use Tests\Domain\SqliteEntityManagerTrait;

/**
 * Every entity but Sequence used to map its string primAcc as a relation to Sequence : flushing a
 * parsed Feature, Reference, Author, Keyword or GbSequence failed with a TypeError, so these tables
 * could never hold a single row. Their composite keys also allowed one author per reference, one
 * row per feature key and qualifier, and one DR line per entry.
 */
class ParsedRecordPersistenceTest extends TestCase
{
    use SqliteEntityManagerTrait;

    public function testAParsedGenbankRecordIsStoredWhole()
    {
        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile(file('data/human.seq'));

        $oEm = $this->createEntityManagerWithSchema();
        $oEm->persist($oParser->getSequence());
        $oEm->persist($oParser->getGbSequence());
        foreach (array_merge($oParser->getFeatures(), $oParser->getReferences(), $oParser->getAuthors(), $oParser->getKeywords() ?? []) as $oRow) {
            $oEm->persist($oRow);
        }
        $oEm->flush();
        $oEm->clear();

        // NM_031438 is 9 characters long, one more than the former prim_acc column.
        $this->assertEquals(3488, strlen($oEm->find(Sequence::class, "NM_031438")->getSequence()));
        $this->assertEquals("NM_031438.4", $oEm->find(GbSequence::class, "NM_031438")->getVersion());
        $this->assertCount(count($oParser->getFeatures()), $oEm->getRepository(Feature::class)->findAll());
        $this->assertCount(9, $oEm->getRepository(Reference::class)->findBy(["primAcc" => "NM_031438"]));
        // Reference 1 has five authors.
        $this->assertCount(5, $oEm->getRepository(Author::class)->findBy(["primAcc" => "NM_031438", "refno" => 1]));
    }

    public function testAParsedSwissprotRecordIsStoredWhole()
    {
        $oParser = new ParseSwissprotManager();
        $oParser->parseDataFile(file('data/Q5K4E3.txt'));

        $oEm = $this->createEntityManagerWithSchema();
        $oEm->persist($oParser->getSequence());
        foreach (array_merge($oParser->getFeatures(), $oParser->getReferences(), $oParser->getAuthors(), $oParser->getSpDatabank(), $oParser->getAccession()) as $oRow) {
            $oEm->persist($oRow);
        }
        $oEm->flush();
        $oEm->clear();

        $this->assertCount(32, $oEm->getRepository(Feature::class)->findAll());
        $this->assertCount(157, $oEm->getRepository(Author::class)->findBy(["refno" => 2]));
        $aDatabanks = $oEm->getRepository(SpDatabank::class)->findBy(["primAcc" => "Q5K4E3"]);
        $this->assertCount(count($oParser->getSpDatabank()), $aDatabanks);
        $this->assertEquals("CAF25303.1", $aDatabanks[0]->getPid2());
    }
}
