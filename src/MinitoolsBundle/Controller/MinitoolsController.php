<?php
/**
 * Minitools controller
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 february 2019
 * Last modified 23 february 2019
 */
namespace MinitoolsBundle\Controller;

use MinitoolsBundle\Entity\DnaToProtein;
use MinitoolsBundle\Service\DnaToProteinManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use MinitoolsBundle\Form\DnaToProteinType;

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
     * @param   Request                 $request
     * @param   DnaToProteinManager     $dnaToProteinManager
     * @return  Response
     * @throws  \Exception
     */
    public function dnaToProteinAction(Request $request, DnaToProteinManager $dnaToProteinManager)
    {
        $sRvSequence    = "";
        $aFrames        = [];
        $sResults       = "";

        $aAminoAcidCodes        = $this->getParameter('codons');
        $aAminoAcidCodesLeft    = array_slice($aAminoAcidCodes, 0, 13);
        $aAminoAcidCodesRight   = array_slice($aAminoAcidCodes, 13);

        $dnatoprotein = new DnaToProtein();
        $form = $this->get('form.factory')->create(DnaToProteinType::class, $dnatoprotein);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $sequence = preg_replace("(\W|\d)", "", $dnatoprotein->getSequence());

            if($dnatoprotein->getUsemycode() == 1) {
                $mycode = preg_replace("([^FLIMVSPTAY*HQNKDECWRG\*])", "", $dnatoprotein->getMycode());
                if (strlen($mycode) != 64) {
                    throw new \Exception("The custom code is not correct (is not 64 characters long).");
                }
                $dnatoprotein->setGeneticCode("custom");
            }

            if ($dnatoprotein->getProtsize() < 10) {
                throw new \Exception("Minimum size of protein sequence is not correct (minimum size is 10).");
            }

            if ($dnatoprotein->getGeneticCode() == "custom"){
                // Translate in  5-3 direction
                $aFrames[1] = $dnaToProteinManager->translateDNAToProteinCustomcode(substr($sequence, 0, floor(strlen($sequence)/3)*3), $mycode);

                if ($dnatoprotein->getFrames() > 1){
                    $aFrames[2] = $dnaToProteinManager->translateDNAToProteinCustomcode(substr($sequence, 1,floor((strlen($sequence)-1)/3)*3),$mycode);
                    $aFrames[3] = $dnaToProteinManager->translateDNAToProteinCustomcode(substr($sequence, 2,floor((strlen($sequence)-2)/3)*3),$mycode);
                }
                // Translate the complementary sequence
                if ($dnatoprotein->getFrames() > 3){
                    // Get complementary
                    $sRvSequence = $dnaToProteinManager->revCompDNA($sequence);
                    $aFrames[4] = $dnaToProteinManager->translateDNAToProteinCustomcode(substr($sRvSequence, 0, floor(strlen($sRvSequence)/3)*3),$mycode);
                    $aFrames[5] = $dnaToProteinManager->translateDNAToProteinCustomcode(substr($sRvSequence, 1,floor((strlen($sRvSequence)-1)/3)*3),$mycode);
                    $aFrames[6] = $dnaToProteinManager->translateDNAToProteinCustomcode(substr($sRvSequence, 2,floor((strlen($sRvSequence)-2)/3)*3),$mycode);
                }
            } else {
                // Translate in 5-3 direction
                $aFrames[1] = $dnaToProteinManager->translateDNAToProtein(substr($sequence, 0, floor(strlen($sequence)/3)*3),$dnatoprotein->getGeneticCode());
                if ($dnatoprotein->getFrames() > 1){
                    $aFrames[2] = $dnaToProteinManager->translateDNAToProtein(substr($sequence, 1,floor((strlen($sequence)-1)/3)*3),$dnatoprotein->getGeneticCode());
                    $aFrames[3] = $dnaToProteinManager->translateDNAToProtein(substr($sequence, 2,floor((strlen($sequence)-2)/3)*3),$dnatoprotein->getGeneticCode());
                }
                // Translate the complementary sequence
                if ($dnatoprotein->getFrames() > 3){
                    // Get complementary
                    $sRvSequence = $dnaToProteinManager->revCompDNA($sequence);
                    //calculate frames 4-6
                    $aFrames[4] = $dnaToProteinManager->translateDNAToProtein(substr($sRvSequence, 0,floor(strlen($sRvSequence)/3)*3),$dnatoprotein->getGeneticCode());
                    $aFrames[5] = $dnaToProteinManager->translateDNAToProtein(substr($sRvSequence, 1,floor((strlen($sRvSequence)-1)/3)*3),$dnatoprotein->getGeneticCode());
                    $aFrames[6] = $dnaToProteinManager->translateDNAToProtein(substr($sRvSequence, 2,floor((strlen($sRvSequence)-2)/3)*3),$dnatoprotein->getGeneticCode());
                }
            }
            // SHOW TRANSLATIONS ALIGNED (when requested)
            if ((bool)$dnatoprotein->getShowAligned()){
                $sResults = $dnaToProteinManager->showTranslationsAligned($sequence,$sRvSequence,$aFrames);
            }
            // FIND ORFs
            if ((bool)$dnatoprotein->getSearchOrfs()){
                $aFrames = $dnaToProteinManager->findORF($aFrames, $dnatoprotein->getProtsize(), (bool)$dnatoprotein->getOnlyCoding(), (bool)$dnatoprotein->getTrimmed());
            }
        }

        return $this->render(
            '@Minitools/Minitools/dnaToProtein.html.twig',
            [
                'amino_left'        => $aAminoAcidCodesLeft,
                'amino_right'       => $aAminoAcidCodesRight,
                'form'              => $form->createView(),
                'frames'            => $aFrames,
                'aligned_results'   => $sResults
            ]
        );
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