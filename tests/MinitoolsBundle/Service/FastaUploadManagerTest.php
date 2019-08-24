<?php
namespace Tests\MinitoolsBundle\Service;

use MinitoolsBundle\Service\FastaUploaderManager;
use PHPUnit\Framework\TestCase;

class FastaUploadManagerTest extends TestCase
{
    public function testIsValidSequenceTrue()
    {
        $sSeq = "GGCAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGT";

        $service = new FastaUploaderManager();
        $testFunction = $service->isValidSequence($sSeq);

        $this->assertTrue($testFunction);
    }

    public function testIsValidSequenceTrueException()
    {
        $this->expectException(\Exception::class);
        $sSeq = [];
        $service = new FastaUploaderManager();
        $service->isValidSequence($sSeq);
    }

    public function testIsValidSequenceFalse()
    {
        $sSeq = "GGCAGATTCCPCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGT";

        $service = new FastaUploaderManager();
        $testFunction = $service->isValidSequence($sSeq);

        $this->assertFalse($testFunction);
    }

    public function testCheckNucleotidSequence()
    {
        $this->expectException(\Exception::class);
        $service = new FastaUploaderManager();
        $a = $t = $c = $g = 0;
        $service->checkNucleotidSequence("pom pom lalala", $a, $g, $t, $c, 4);
    }
}