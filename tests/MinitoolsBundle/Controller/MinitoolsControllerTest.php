<?php
/**
 * Functional tests for Minitools controller
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 23 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundleTest\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Response;

class MinitoolsControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }


    public function testChaosGameRepresentationActionFCGR()
    {
        $this->client->request('GET', '/minitools/chaos-game-representation/FCGR');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }
/*
    public function testChaosGameRepresentationActionCGR()
    {
        $this->client->request('GET', '/minitools/chaos-game-representation/CGR');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testDistanceAmongSequencesAction()
    {
        $this->client->request('GET', '/minitools/distance-among-sequences');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testDnaToProteinAction()
    {
        $this->client->request('GET', '/minitools/dna-to-protein');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testFindPalindromesAction()
    {
        $this->client->request('GET', '/minitools/find-palindromes');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testFastaUploaderAction()
    {
        $this->client->request('GET', '/minitools/fasta-uploader');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testMeltingTemperatureAction()
    {
        $this->client->request('GET', '/minitools/melting-temperature');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testMicroArrayAnalysisAdaptiveQuantificationAction()
    {
        $this->client->request('GET', '/minitools/micro-array-analysis-adaptive-quantification');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testMicrosatelliteRepeatsFinderAction()
    {
        $this->client->request('GET', '/minitools/microsatellite-repeats-finder');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testOligonucleotideFrequencyAction()
    {
        $this->client->request('GET', '/minitools/oligonucleotide-frequency');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testPcrAmplificationAction()
    {
        $this->client->request('GET', '/minitools/pcr-amplification');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testProteinPropertiesAction()
    {
        $this->client->request('GET', '/minitools/protein-properties');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testProteinToDnaAction()
    {
        $this->client->request('GET', '/minitools/protein-to-dna');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testRandomSeqsAction()
    {
        $this->client->request('GET', '/minitools/random-seqs');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testReduceProteinAlphabetAction()
    {
        $this->client->request('GET', '/minitools/reduce-protein-alphabet');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testRestrictionDigestAction()
    {
        $this->client->request('GET', '/minitools/restriction-digest');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testSeqAlignmentAction()
    {
        $this->client->request('GET', '/minitools/seq-alignment');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testSequencesManipulationAndDataAction()
    {
        $this->client->request('GET', '/minitools/sequences-manipulation-and-data');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testSkewsAction()
    {
        $this->client->request('GET', '/minitools/skews');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }


    public function testFormulasAction()
    {
        $this->client->request('GET', '/minitools/skews');

        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }*/
}