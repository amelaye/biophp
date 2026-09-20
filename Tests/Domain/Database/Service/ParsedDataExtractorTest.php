<?php
namespace Tests\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Service\ParsedDataExtractor;
use Amelaye\BioPHP\Domain\Parser\Entity\PdbAtom;
use Amelaye\BioPHP\Domain\Parser\Entity\PrositeDbRef;
use PHPUnit\Framework\TestCase;

class ParsedDataExtractorTest extends TestCase
{
    public function testGettersBecomeCamelCaseKeys()
    {
        $oRef = new PrositeDbRef();
        $oRef->setAccession("P12345");
        $oRef->setEntryName("TEST_HUMAN");
        $oRef->setTruePositive(true);

        $this->assertSame(
            ["accession" => "P12345", "entryName" => "TEST_HUMAN", "truePositive" => true],
            ParsedDataExtractor::extract($oRef)
        );
    }

    public function testNestedObjectsAndArraysAreReadRecursively()
    {
        $oAtom = new PdbAtom();
        $oAtom->setName("CA");
        $oAtom->setX(1.5);

        $oSource = new class($oAtom) {
            public function __construct(private $oAtom) {}
            public function getAtoms(): array { return [$this->oAtom]; }
            public function getLabels(): array { return ["a" => "x", "b" => ["y"]]; }
        };

        $aData = ParsedDataExtractor::extract($oSource);
        $this->assertSame("CA", $aData["atoms"][0]["name"]);
        $this->assertSame(1.5, $aData["atoms"][0]["x"]);
        $this->assertSame(["a" => "x", "b" => ["y"]], $aData["labels"]);
    }

    public function testOnlyParameterlessNonStaticGettersAreRead()
    {
        $oSource = new class {
            public function getKept(): string { return "yes"; }
            public function getNeedsArgument(string $s): string { return $s; }
            public static function getStatic(): string { return "static"; }
            public function setSomething(string $s): void {}
            public function compute(): string { return "not a getter"; }
        };

        $this->assertSame(["kept" => "yes"], ParsedDataExtractor::extract($oSource));
    }

    public function testDatesEnumsAndTraversablesAreConverted()
    {
        $oSource = new class {
            public function getWhen(): \DateTimeInterface { return new \DateTimeImmutable("2026-09-20T10:00:00+00:00"); }
            public function getKind(): \Tests\Domain\Database\Service\ExtractorKind { return \Tests\Domain\Database\Service\ExtractorKind::Dna; }
            public function getLazy(): \Generator { yield "k" => 1; yield "l" => 2; }
        };

        $this->assertSame(
            ["when" => "2026-09-20T10:00:00+00:00", "kind" => "dna", "lazy" => ["k" => 1, "l" => 2]],
            ParsedDataExtractor::extract($oSource)
        );
    }

    public function testValuesJsonCannotEncodeAreMadeSafe()
    {
        $oSource = new class {
            public function getBroken(): string { return "AT\xC3\x28GC"; }
            public function getInfinite(): float { return INF; }
            public function getNotANumber(): float { return NAN; }
        };

        $aData = ParsedDataExtractor::extract($oSource);
        $this->assertNull($aData["infinite"]);
        $this->assertNull($aData["notANumber"]);
        $this->assertIsString(json_encode($aData, JSON_THROW_ON_ERROR));
    }

    public function testAnObjectReferringToItselfDoesNotLoopForever()
    {
        $oSource = new class {
            public $oSelf;
            public function getName(): string { return "loop"; }
            public function getSelf() { return $this->oSelf; }
        };
        $oSource->oSelf = $oSource;

        $this->assertSame(["name" => "loop", "self" => null], ParsedDataExtractor::extract($oSource));
    }

    public function testAnObjectWithoutGettersFallsBackToItsStringForm()
    {
        $oSource = new class {
            public function getId()
            {
                return new class implements \Stringable {
                    public function __toString(): string { return "ID-1"; }
                };
            }
        };

        $this->assertSame(["id" => "ID-1"], ParsedDataExtractor::extract($oSource));
    }
}

enum ExtractorKind: string
{
    case Dna = "dna";
}
