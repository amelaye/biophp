<?php
/**
 * Minitools controller
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 february 2019
 * Last modified 23 february 2019
 */
namespace MinitoolsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class MinitoolsController extends Controller
{
    /**
     * @Route("/minitools/chaos-game-representation", name="chaos_game_representation")
     */
    public function chaosGameRepresentationAction()
    {
        return $this->render('@Minitools/Minitools/chaosGameRepresentation.html.twig');
    }

    /**
     * @Route("/minitools/distance-among-sequences", name="distance_among_sequences")
     */
    public function distanceAmongSequencesAction()
    {
        return $this->render('@Minitools/Minitools/distanceAmongSequences.html.twig');
    }

    /**
     * @Route("/minitools/dna-to-protein", name="dna_to_protein")
     */
    public function dnaToProteinAction()
    {
        return $this->render('@Minitools/Minitools/dnaToProtein.html.twig');
    }

    /**
     * @Route("/minitools/find-palindromes", name="find_palindromes")
     */
    public function findPalindromesAction()
    {
        return $this->render('@Minitools/Minitools/findPalindromes.html.twig');
    }

    /**
     * @Route("/minitools/gc-content-finder", name="gc_content_finder")
     */
    public function gcContentFinderAction()
    {
        return $this->render('@Minitools/Minitools/gcContentFinder.html.twig');
    }

    /**
     * @Route("/minitools/melting-temperature", name="melting_temperature")
     */
    public function meltingTemperatureAction()
    {
        return $this->render('@Minitools/Minitools/meltingTemperature.html.twig');
    }

    /**
     * @Route("/minitools/micro-array-analysis-adaptive-quantification", name="micro_array_analysis_adaptive_quantification")
     */
    public function microArrayAnalysisAdaptiveQuantificationAction()
    {
        return $this->render('@Minitools/Minitools/microArrayAnalysisAdaptiveQuantification.html.twig');
    }

    /**
     * @Route("/minitools/microsatellite-repeats-finder", name="microsatellite_repeats_finder")
     */
    public function microsatelliteRepeatsFinderAction()
    {
        return $this->render('@Minitools/Minitools/microsatelliteRepeatsFinder.html.twig');
    }

    /**
     * @Route("/minitools/oligonucleotide-frequency", name="oligonucleotide_frequency")
     */
    public function oligonucleotideFrequencyAction()
    {
        return $this->render('@Minitools/Minitools/oligonucleotideFrequency.html.twig');
    }

    /**
     * @Route("/minitools/pcr-amplification", name="pcr_amplification")
     */
    public function pcrAmplificationAction()
    {
        return $this->render('@Minitools/Minitools/pcrAmplification.html.twig');
    }

    /**
     * @Route("/minitools/protein-properties", name="protein_properties")
     */
    public function proteinPropertiesAction()
    {
        return $this->render('@Minitools/Minitools/proteinProperties.html.twig');
    }

    /**
     * @Route("/minitools/protein-to-dna", name="protein_to_dna")
     */
    public function proteinToDnaAction()
    {
        return $this->render('@Minitools/Minitools/proteinToDna.html.twig');
    }

    /**
     * @Route("/minitools/random-seqs", name="random_seqs")
     */
    public function randomSeqsAction()
    {
        return $this->render('@Minitools/Minitools/randomSeqs.html.twig');
    }

    /**
     * @Route("/minitools/reader-gff-fasta", name="reader_gff_fasta")
     */
    public function readerGffFastaAction()
    {
        return $this->render('@Minitools/Minitools/readerGffFasta.html.twig');
    }

    /**
     * @Route("/minitools/reduce-protein-alphabet", name="reduce_protein_alphabet")
     */
    public function reduceProteinAlphabetAction()
    {
        return $this->render('@Minitools/Minitools/reduceProteinAlphabet.html.twig');
    }

    /**
     * @Route("/minitools/restriction-digest", name="restriction_digest")
     */
    public function restrictionDigestAction()
    {
        return $this->render('@Minitools/Minitools/restrictionDigest.html.twig');
    }

    /**
     * @Route("/minitools/seq-alignment", name="seq_alignment")
     */
    public function seqAlignmentAction()
    {
        return $this->render('@Minitools/Minitools/seqAlignment.html.twig');
    }

    /**
     * @Route("/minitools/sequences-manipulation-and-data", name="sequences_manipulation_and_data")
     */
    public function sequencesManipulationAndDataAction()
    {
        return $this->render('@Minitools/Minitools/sequencesManipulationAndData.html.twig');
    }

    /**
     * @Route("/minitools/skews", name="skews")
     */
    public function skewsAction()
    {
        return $this->render('@Minitools/Minitools/skews.html.twig');
    }

    /**
     * @Route("/minitools/useful-formulas", name="useful_formulas")
     */
    public function usefulFormulasAction()
    {
        return $this->render('@Minitools/Minitools/usefulFormulas.html.twig');
    }
}