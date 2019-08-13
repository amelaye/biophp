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

    public function testIsValidSequenceFalse()
    {
        $sSeq = "GGCAGATTCCPCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGT";

        $service = new FastaUploaderManager();
        $testFunction = $service->isValidSequence($sSeq);

        $this->assertFalse($testFunction);
    }
}