<?php
/**
 * Functional tests for Minitools controller
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 26 march 2019
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


    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas, and checks if the picture is generated
     */
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

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testChaosGameRepresentationActionCGR()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/chaos-game-representation/CGR');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testDistanceAmongSequencesAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/distance-among-sequences');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testDnaToProteinAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/dna-to-protein');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testFindPalindromesAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/find-palindromes');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testFastaUploaderAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/fasta-uploader');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testMeltingTemperatureAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $crawler = $this->client->request('GET', '/minitools/melting-temperature');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());

        /**
         * 2 - Posting data OK
         */
        $form = $crawler->selectButton('Calculate Tm')->form();
        $form['melting_temperature[primer]'] = 'AAAATTTGGGGCCCATGCCC'; // primer
        $form['melting_temperature[basic]'] = 1; // Basic Tm (Deg. Nuc. allowed)
        $form['melting_temperature[nearestNeighbor]'] = 1; // Basic Tm (Deg. Nuc. NOT allowed)
        $form['melting_temperature[cp]'] = 200; // Primer concentration
        $form['melting_temperature[cs]'] = 50; // Salt concentration
        $form['melting_temperature[cmg]'] = 0; // Mg2+ concentration

        $crawler = $this->client->submit($form);
        $this->assertGreaterThan(1, $crawler->filter('div#results pre')->count());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testMicroArrayAnalysisAdaptiveQuantificationAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/micro-array-analysis-adaptive-quantification');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testMicrosatelliteRepeatsFinderAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/microsatellite-repeats-finder');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testOligonucleotideFrequencyAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/oligonucleotide-frequency');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testPcrAmplificationAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/pcr-amplification');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testProteinPropertiesAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/protein-properties');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testProteinToDnaAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/protein-to-dna');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testRandomSeqsAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/random-seqs');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testReduceProteinAlphabetAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/reduce-protein-alphabet');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testRestrictionDigestAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/restriction-digest');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testSeqAlignmentAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/seq-alignment');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testSequencesManipulationAndDataAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/sequences-manipulation-and-data');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testSkewsAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/skews');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Two steps :
     * 1) Checks if page goes on 200 status
     * 2) Posts sample datas
     */
    public function testFormulasAction()
    {
        /**
         * 1 - Access to the page OK
         */
        $this->client->request('GET', '/minitools/skews');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
