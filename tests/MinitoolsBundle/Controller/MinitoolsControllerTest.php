<?php
/**
 * Functional tests for Minitools controller
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 23 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundleTest\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class MinitoolsControllerTest
 * @package MinitoolsBundleTest\Controller
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MinitoolsControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }


    public function testChaosGameRepresentationActionFCGR()
    {
        /**
         * 1 - Access to the page OK
         */
        $crawler = $this->client->request('GET', '/minitools/chaos-game-representation/FCGR');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());

        /**
         * 2 - Posting data OK
         */
        $sampleADN = "GTGCCGAGCTGAGTTCCTTATAAGAATTAATCTTAATTTTGTATTTTTTCCTGTAAGACAATAGGCCATG";
        $sampleADN .= "TTAATTAAACTGAAGAAGGATATATTTGGCTGGGTGTTTTCAAATGTCAGCTTAAAATTGGTAATTGAAT";
        $sampleADN .= "GGAAGCAAAATTATAAGAAGAGGAAATTAAAGTCTTCCATTGCATGTATTGTAAACAGAAGGAGATGGGT";
        $sampleADN .= "GATTCCTTCAATTCAAAAGCTCTCTTTGGAATGAACAATGTGGGCGTTTGTAAATTCTGGAAATGTCTTT";
        $sampleADN .= "CTATTCATAATAAACTAGATACTGTTGATCTTTTAAAAAAAAAAAA";

        $form = $crawler->selectButton('Create FCGR image')->form();
        $form['chaos_game_representation[seq_name]'] = 'test'; // name of the seq
        $form['chaos_game_representation[size]'] = 'auto'; // we don't care
        $form['chaos_game_representation[s]'] = 2; // Both strands
        $form['chaos_game_representation[len]'] = 2; // Search oligos of length
        $form['chaos_game_representation[seq]'] = $sampleADN; // Sequence code
        $form['chaos_game_representation[map]'] = 1; // Show as image map
        $form['chaos_game_representation[freq]'] = 1; // Show as oligonucleotide frequencies

        $crawler = $this->client->submit($form);

        $this->assertSame(1, $crawler->filter('div#chaosGameFCGR')->count());
    }

    public function testChaosGameRepresentationActionCGR()
    {
        $this->client->request('GET', '/minitools/chaos-game-representation/CGR');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testDistanceAmongSequencesAction()
    {
        $this->client->request('GET', '/minitools/distance-among-sequences');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testDnaToProteinAction()
    {
        $this->client->request('GET', '/minitools/dna-to-protein');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testFindPalindromesAction()
    {
        $this->client->request('GET', '/minitools/find-palindromes');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testFastaUploaderAction()
    {
        $this->client->request('GET', '/minitools/fasta-uploader');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testMeltingTemperatureAction()
    {
        $this->client->request('GET', '/minitools/melting-temperature');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testMicroArrayAnalysisAdaptiveQuantificationAction()
    {
        $this->client->request('GET', '/minitools/micro-array-analysis-adaptive-quantification');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testMicrosatelliteRepeatsFinderAction()
    {
        $this->client->request('GET', '/minitools/microsatellite-repeats-finder');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testOligonucleotideFrequencyAction()
    {
        $this->client->request('GET', '/minitools/oligonucleotide-frequency');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testPcrAmplificationAction()
    {
        $this->client->request('GET', '/minitools/pcr-amplification');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testProteinPropertiesAction()
    {
        $this->client->request('GET', '/minitools/protein-properties');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testProteinToDnaAction()
    {
        $this->client->request('GET', '/minitools/protein-to-dna');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testRandomSeqsAction()
    {
        $this->client->request('GET', '/minitools/random-seqs');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testReduceProteinAlphabetAction()
    {
        $this->client->request('GET', '/minitools/reduce-protein-alphabet');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testRestrictionDigestAction()
    {
        $this->client->request('GET', '/minitools/restriction-digest');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testSeqAlignmentAction()
    {
        $this->client->request('GET', '/minitools/seq-alignment');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testSequencesManipulationAndDataAction()
    {
        $this->client->request('GET', '/minitools/sequences-manipulation-and-data');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testSkewsAction()
    {
        $this->client->request('GET', '/minitools/skews');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testFormulasAction()
    {
        $this->client->request('GET', '/minitools/skews');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
