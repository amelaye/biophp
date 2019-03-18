<?php
/**
 * Minitools controller
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 february 2019
 * Last modified 11 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Controller;

use AppBundle\Service\OligosManager;
use MinitoolsBundle\Entity\DistanceAmongSequences;
use MinitoolsBundle\Entity\FindPalindromes;
use MinitoolsBundle\Form\DistanceAmongSequencesType;
use MinitoolsBundle\Form\FindPalindromesType;
use MinitoolsBundle\Service\ChaosGameRepresentationManager;
use MinitoolsBundle\Service\DistanceAmongSequencesManager;
use MinitoolsBundle\Service\FindPalindromeManager;
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
     * @param OligosManager $oligosManager
     * @return Response
     * @throws \Exception
     */
    public function chaosGameRepresentationAction(
        $schema,
        Request $request,
        ChaosGameRepresentationManager $chaosGameReprentationManager,
        OligosManager $oligosManager
    )
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
                $aOligos = $oligosManager->findOligos(
                    $aSeqData["sequence"],
                    $aSeqData["length"],
                    array_values($this->getParameter('dna_complements'))
                );

                // CREATE CHAOS GAME REPRESENTATION OF FREQUENCIES IMAGE
                //      check the function for more info on parameters
                //      $data contains a string with the data to be used to create the image map
                $for_map = $chaosGameReprentationManager->createFCGRImage(
                    $aOligos,
                    $oChaosGameRepresentation->getSeqName(),
                    $aNucleotides,
                    strlen($oChaosGameRepresentation->getSeq()),
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
     * @param   Request                         $request
     * @param   DistanceAmongSequencesManager   $oDistanceAmongSequencesManager
     * @param   OligosManager                   $oligosManager
     * @return  Response
     * @throws \Exception
     */
    public function distanceAmongSequencesAction(
        Request $request,
        DistanceAmongSequencesManager $oDistanceAmongSequencesManager,
        OligosManager $oligosManager
    )
    {
        $oDistanceAmongSequences = new DistanceAmongSequences();
        $form = $this->get('form.factory')->create(DistanceAmongSequencesType::class, $oDistanceAmongSequences);

        $oligo_array = [];
        $data = [];
        $textcluster = "";
        $seq_name = "";
        $dendogramFile = "";

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $oDistanceAmongSequencesManager->setDistanceAmongSequence($oDistanceAmongSequences);

            $allsequences = $oDistanceAmongSequences->getSeq();

            //remove a couple of things from sequence
            $allsequences = substr($allsequences,strpos($allsequences,">"));      // whatever is before ">", which is the start of the first sequence
            $allsequences = preg_replace("/\r/","",$allsequences);    // remove carriage returns ("\r"), but do not remove line feeds ("\n")

            $seqs = preg_split("/>/",$allsequences,-1,PREG_SPLIT_NO_EMPTY);

            // get the name of each sequence (save names to array $seq_name)
            foreach ($seqs as $key => $val) {
                $seq_name[$key] = substr($val,0,strpos($val,"\n"));
                $temp_val = substr($val,strpos($val,"\n"));
                $temp_val = preg_replace("/\W|\d/","",$temp_val);
                $seqs[$key] = strtoupper($temp_val);
            }
            // at this moment two arrays are available: $seqs (with sequences) and $seq_names (with name of sequences)
            // EUCLIDEAN DISTANCE
            if ($oDistanceAmongSequences->getMethod() == "euclidean") {
                $seq_and_revseq = "";

                // COMPUTE OLIGONUCLEOTIDE FREQUENCIES
                foreach ($seqs as $key => $val) {
                    // to compute oligonucleotide frequencies, both strands are used
                    $valRevert = strrev($val);
                    foreach ($this->getParameter('dna_complements') as $nucleotide => $complement) {
                        $valRevert = str_replace($nucleotide, strtolower($complement), $valRevert);
                    }
                    $seq_and_revseq = $val." ".strtoupper($valRevert);

                    $oligos = $oligosManager->findOligos(
                        $seq_and_revseq,
                        $oDistanceAmongSequences->getLen(),
                        array_values($this->getParameter('dna_complements'))
                    );

                    $oligo_array[$key] = $oDistanceAmongSequencesManager->standardFrecuencies($oligos);
                }


                // COMPUTE DISTANCES AMONG SEQUENCES
                //    by computing Euclidean distance
                //    standarized oligonucleotide frequencies in $oligo_array are used, and distances are stored in $data array
                foreach ($seqs as $key => $val) {
                    foreach($seqs as $key2 => $val2) {
                        if ($key >= $key2) {
                            continue;
                        }
                        $data[$key][$key2] = $oDistanceAmongSequencesManager->euclidDistance(
                            $oligo_array[$key],
                            $oligo_array[$key2]
                        );
                    }
                }
            } else {
                // COMPUTE OLIGONUCLEOTIDE FREQUENCIES
                foreach ($seqs as $key => $theseq) {
                    $aComputeZscores = $oDistanceAmongSequencesManager->computeZscoresForTetranucleotides($theseq);
                    $oligo_array[$key] = $oligosManager->findOligos(
                        $aComputeZscores,
                        4,
                        array_values($this->getParameter('dna_complements'))
                    );
                }
                // COMPUTE DISTANCES AMONG SEQUENCES
                //    by computing Pearson distance
                //    standarized oligonucleotide frequencies in $oligo_array are used, and distances are stored in $data array
                foreach($seqs as $key => $val){
                    foreach($seqs as $key2 => $val2){
                        if ($key >= $key2) {
                            continue;
                        }
                        $data[$key][$key2]= $oDistanceAmongSequencesManager->pearsonDistance(
                            $oligo_array[$key],
                            $oligo_array[$key2]
                        );
                    }
                }

            }

            /*
             * NEXT LINES WILL PERFORM UPGMA CLUSTERING
             * in each loop, array $data is reduced (one case per loop)
             */
            while (sizeof($data) > 1) {
                $min = $oDistanceAmongSequencesManager->minArray($data);
                $comp[$oDistanceAmongSequencesManager->getX()][$oDistanceAmongSequencesManager->getY()] = $min;
                $data = $oDistanceAmongSequencesManager->newArray($data);
            }

            $min = $oDistanceAmongSequencesManager->minArray($data);

            $x = $oDistanceAmongSequencesManager->getX();
            $y = $oDistanceAmongSequencesManager->getY();

            /*
             * end of clustering
             * array $comp stores the important data
             */
            $comp[$x][$y] = $min;

            /*
             * $textcluster is the results of the cluster as text.
             * p.e.:  ((3,4),7),(((5,6),1),2)
             */
            $textcluster = $x.",".$y;

            $dendogramFile = $this->getParameter('nucleotids_graphs')['dendogram_file'];

            // CREATE THE IMAGE WITH THE DENDROGRAM
            $oDistanceAmongSequencesManager->createDendrogram($textcluster, $comp, $dendogramFile);
        }


        return $this->render(
            '@Minitools/Minitools/distanceAmongSequences.html.twig',
            [
                'form'              => $form->createView(),
                'oligo_array'       => $oligo_array,
                'data'              => $data,
                'length'            => $oDistanceAmongSequences->getLen(),
                'seq_names'         => $seq_name,
                'textcluster'       => $textcluster,
                'dendogram_file'    => $dendogramFile
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
     * @param Request $request
     * @param FindPalindromeManager $oFindPalindromeManager
     * @return Response
     * @throws \Exception
     */
    public function findPalindromesAction(Request $request, FindPalindromeManager $oFindPalindromeManager)
    {
        $aPalindromes = [];
        $oFindPalindromes = new FindPalindromes();
        $form = $this->get('form.factory')->create(FindPalindromesType::class, $oFindPalindromes);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $sSequence = $oFindPalindromeManager->removeUselessFromDNA($oFindPalindromes->getSeq());
            if($sSequence == "") {
                throw new \Exception("No sequence available");
            }
            $aPalindromes = $oFindPalindromeManager->findPalindromicSeqs(
                $sSequence,
                $oFindPalindromes->getMin(),
                $oFindPalindromes->getMax()
            );
        }

        return $this->render(
            '@Minitools/Minitools/findPalindromes.html.twig',
            [
                'form'          => $form->createView(),
                'min'           => $oFindPalindromes->getMin(),
                'max'           => $oFindPalindromes->getMax(),
                'palindromes'   => $aPalindromes
            ]
        );
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