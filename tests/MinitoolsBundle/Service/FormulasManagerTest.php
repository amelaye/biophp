<?php
namespace Tests\MinitoolsBundle\Service;

use PHPUnit\Framework\TestCase;
use MinitoolsBundle\Service\FormulasManager;

class FormulasManagerTest extends TestCase
{
    public function testMWOfDsDNA()
    {
        $sequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $iExpected = 39600;

        $service = new FormulasManager();
        $testFunction = $service->mwOfDsDNA($sequence);

        $this->assertEquals($iExpected, $testFunction);
    }

    public function testMWOfSsDNA()
    {
        $sequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $iExpected = 19800;

        $service = new FormulasManager();
        $testFunction = $service->mwOfSsDNA($sequence);

        $this->assertEquals($iExpected, $testFunction);
    }

    public function testPmolOfDsDNA()
    {
        $pmol_dsDNA_sequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $pmol_dsDNA_no_of_mueg = 23;
        $fExpected = 1161.6161616162;

        $service = new FormulasManager();
        $testFunction = $service->pmolOfDsDNA($pmol_dsDNA_sequence, $pmol_dsDNA_no_of_mueg);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testPmolOfSsDNA()
    {
        $sPmolDsDNASequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $iPmolDsDNANbMueg = 23;
        $fExpected = 1161.6161616162;

        $service = new FormulasManager();
        $testFunction = $service->pmolOfSsDNA($sPmolDsDNASequence, $iPmolDsDNANbMueg);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testMicroToPmolDsDNA()
    {
        $pmol_dsDNA_sequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $no_of_micro_dsDNA = 23;
        $fExpected = 580.75;

        $service = new FormulasManager();
        $testFunction = $service->microToPmolDsDNA($pmol_dsDNA_sequence, $no_of_micro_dsDNA);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testMicroToPmolSsDNA()
    {
        $pmol_ssDNA_sequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $no_of_micro_ssDNA = 23;
        $fExpected = 1161.5;

        $service = new FormulasManager();
        $testFunction = $service->microToPmolSsDNA($pmol_ssDNA_sequence, $no_of_micro_ssDNA);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testPmolToMicroDsDNA()
    {
        $micro_dsDNA_sequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $no_of_pmol_dsDNA = 23;
        $fExpected = 0.9108;

        $service = new FormulasManager();
        $testFunction = $service->pmolToMicroDsDNA($micro_dsDNA_sequence, $no_of_pmol_dsDNA);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testPmolToMicroSsDNA()
    {
        $micro_ssDNA_sequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $no_of_pmol_ssDNA = 23;
        $fExpected = 0.4554;

        $service = new FormulasManager();
        $testFunction = $service->pmolToMicroSsDNA($micro_ssDNA_sequence, $no_of_pmol_ssDNA);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testMwOfSsRNA()
    {
        $sequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $iExpected = 20400;

        $service = new FormulasManager();
        $testFunction = $service->mwOfSsRNA($sequence);

        $this->assertEquals($iExpected, $testFunction);
    }

    public function testPmolOfSsRNA()
    {
        $pmol_ssRNA_sequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $pmol_ssRNA_no_of_mueg = 23;
        $iExpected = 1127.3833333333;

        $service = new FormulasManager();
        $testFunction = $service->pmolOfSsRNA($pmol_ssRNA_sequence, $pmol_ssRNA_no_of_mueg);

        $this->assertEquals($iExpected, $testFunction);
    }

    public function testPmolToMicroSsRNA()
    {
        $micro_ssRNA_sequence = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG";
        $no_of_pmol_ssRNA = 23;
        $iExpected = 0.4692;

        $service = new FormulasManager();
        $testFunction = $service->pmolToMicroSsRNA($micro_ssRNA_sequence, $no_of_pmol_ssRNA);

        $this->assertEquals($iExpected, $testFunction);
    }

    public function testCentiToFahren()
    {
        $centigrade = 20;
        $fExpected = 43.1;

        $service = new FormulasManager();
        $testFunction = $service->centiToFahren($centigrade);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testFarhenToCenti()
    {
        $fahren = 100;
        $fExpected = 37.74;

        $service = new FormulasManager();
        $testFunction = $service->farhenToCenti($fahren);

        $this->assertEquals($fExpected, $testFunction);
    }

    public function testMbarToMmHg()
    {
        $Hg = 10;
        $fExpected = 7.5;

        $service = new FormulasManager();
        $testFunction = $service->mbarToMmHg($Hg);
        $this->assertEquals($fExpected, $testFunction);
    }

    public function testMbarToInchHg()
    {
        $fInchHg = 10;
        $fExpected = 0.394;

        $service = new FormulasManager();
        $testFunction = $service->mbarToInchHg($fInchHg);
        $this->assertEquals($fExpected, $testFunction);
    }

    public function testMbarToPsi()
    {
        $psi = 10;
        $fExpected = 0.145;

        $service = new FormulasManager();
        $testFunction = $service->mbarToPsi($psi);
        $this->assertEquals($fExpected, $testFunction);
    }

    public function testMbarToAtm()
    {
        $atm = 10;
        $fExpected = 0.00987;

        $service = new FormulasManager();
        $testFunction = $service->mbarToAtm($atm);
        $this->assertEquals($fExpected, $testFunction);
    }

    public function testMbarToKPa()
    {
        $kPa = 10;
        $fExpected = 1;

        $service = new FormulasManager();
        $testFunction = $service->mbarToKPa($kPa);
        $this->assertEquals($fExpected, $testFunction);
    }

    public function testMbarToTorr()
    {
        $torr = 10;
        $fExpected = 7.5;

        $service = new FormulasManager();
        $testFunction = $service->mbarToTorr($torr);
        $this->assertEquals($fExpected, $testFunction);
    }
}