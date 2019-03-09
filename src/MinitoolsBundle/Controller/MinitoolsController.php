<?php
/**
 * Minitools controller
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 february 2019
 * Last modified 9 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Controller;

use MinitoolsBundle\Entity\DistanceAmongSequences;
use MinitoolsBundle\Form\DistanceAmongSequencesType;
use MinitoolsBundle\Service\ChaosGameRepresentationManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use MinitoolsBundle\Entity\ChaosGameRepresentation;
use MinitoolsBundle\Entity\DnaToProtein;
use MinitoolsBundle\Entity\Protein;
use MinitoolsBundle\Form\ChaosGameRepresentationType;
use MinitoolsBundle\Form\ProteinPropertiesType;
use MinitoolsBundle\Form\DnaToProteinType;

class MinitoolsController extends Controller
{
    /**
     * @Route("/minitools/chaos-game-representation/{schema}", name="chaos_game_representation")
     * @param Request $request
     * @param ChaosGameRepresentationManager $chaosGameReprentationManager
     * @return Response
     * @throws \Exception
     */
    public function chaosGameRepresentationAction($schema, Request $request, ChaosGameRepresentationManager $chaosGameReprentationManager)
    {
        $oChaosGameRepresentation = new ChaosGameRepresentation();
        $form = $this->get('form.factory')->create(ChaosGameRepresentationType::class, $oChaosGameRepresentation);

        if ($schema == "FCGR") {
            $aOligos = null;
            $for_map = null;
            $aNucleotides = [];

            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

                $chaosGameReprentationManager->setChaosGameRepresentation($oChaosGameRepresentation);

                $aSeqData = $chaosGameReprentationManager->FCGRCompute();

                // compute nucleotide frequencies
                foreach($this->getParameter('dna_complements') as $sNucleotide) {
                    $aNucleotides[$sNucleotide] = substr_count($aSeqData["sequence"], $sNucleotide);
                }

                // COMPUTE OLIGONUCLEOTIDE FREQUENCIES
                //      frequencies are saved to an array named $aOligos
                $aOligos = $chaosGameReprentationManager->findOligos($aSeqData["sequence"], $aSeqData["length"]);

                // CREATE CHAOS GAME REPRESENTATION OF FREQUENCIES IMAGE
                //      check the function for more info on parameters
                //      $data contains a string with the data to be used to create the image map
                $for_map = $chaosGameReprentationManager->createFCGRImage(
                    $aOligos,
                    $oChaosGameRepresentation->getSeqName(),
                    $aNucleotides,
                    $aSeqData["length"],
                    $oChaosGameRepresentation->getS(),
                    $oChaosGameRepresentation->getLen()
                );
            }

            return $this->render(
                '@Minitools/Minitools/chaosGameRepresentationFCGR.html.twig',
                [
                    'form'              => $form->createView(),
                    'oligos'            => $aOligos,
                    'areas'             => $for_map,
                    'is_map'            => $oChaosGameRepresentation->getMap(),
                    'show_as_freq'      => $oChaosGameRepresentation->getFreq()
                ]
            );
        }

        if ($schema == "CGR") {
            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $chaosGameReprentationManager->setChaosGameRepresentation($oChaosGameRepresentation);
                $chaosGameReprentationManager->CGRCompute();
            }

            return $this->render(
                '@Minitools/Minitools/chaosGameRepresentationCGR.html.twig',
                [
                    'form'              => $form->createView(),
                ]
            );
        }
    }

    /**
     * @Route("/minitools/distance-among-sequences", name="distance_among_sequences")
     */
    public function distanceAmongSequencesAction()
    {
        $oDistanceAmongSequences = new DistanceAmongSequences();
        $form = $this->get('form.factory')->create(DistanceAmongSequencesType::class, $oDistanceAmongSequences);

        return $this->render(
            '@Minitools/Minitools/distanceAmongSequences.html.twig',
            [
                'form'              => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/minitools/dna-to-protein", name="dna_to_protein")
     * @param   Request      $request
     * @return  Response
     * @throws  \Exception
     */
    public function dnaToProteinAction(Request $request)
    {
        $aEvent = null;

        $aAminoAcidCodes        = $this->getParameter('codons');
        $aAminoAcidCodesLeft    = array_slice($aAminoAcidCodes, 0, 13);
        $aAminoAcidCodesRight   = array_slice($aAminoAcidCodes, 13);

        $oDnaToProtein = new DnaToProtein();
        $form = $this->get('form.factory')->create(DnaToProteinType::class, $oDnaToProtein);

        // Form treatment
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $aEvent = $this->get('event_dispatcher')->dispatch('on_create_frames', new GenericEvent($oDnaToProtein));
        }

        return $this->render(
            '@Minitools/Minitools/dnaToProtein.html.twig',
            [
                'amino_left'            => $aAminoAcidCodesLeft,
                'amino_right'           => $aAminoAcidCodesRight,
                'form'                  => $form->createView(),
                'frames'                => $aEvent != null ? $aEvent->getArgument('frames') : null,
                'aligned_results'       => $aEvent != null ? $aEvent->getArgument('frames_aligned') : null,
                'aligned_results_compl' => $aEvent != null ? $aEvent->getArgument('frames_aligned_compl') : null,
                'bar'                   => $aEvent != null ? $aEvent->getArgument('bar') : null,
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
        $oProtein = new Protein();
        $form = $this->get('form.factory')->create(ProteinPropertiesType::class, $oProtein);

        return $this->render(
            '@Minitools/Minitools/proteinProperties.html.twig',
            [
                'form'              => $form->createView(),
            ]
        );
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