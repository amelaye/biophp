<?php
/**
 * Minitools controller
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 february 2019
 * Last modified 29 june 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Controller;


use AppBundle\Bioapi\Bioapi;
use AppBundle\Service\OligosManager;

use AppBundle\Traits\OligoTrait;
use MinitoolsBundle\Entity\PcrAmplification;
use MinitoolsBundle\Entity\ProteinToDna;
use MinitoolsBundle\Entity\RandomSequences;
use MinitoolsBundle\Entity\ReduceAlphabet;
use MinitoolsBundle\Entity\RestrictionEnzymeDigest;
use MinitoolsBundle\Entity\SequenceAlignment;
use MinitoolsBundle\Form\OligoNucleotideFrequencyType;
use MinitoolsBundle\Form\PcrAmplificationType;
use MinitoolsBundle\Form\ProteinToDnaType;
use MinitoolsBundle\Form\RandomSequencesType;
use MinitoolsBundle\Form\ReduceAlphabetType;
use MinitoolsBundle\Form\RestrictionEnzymeDigestType;
use MinitoolsBundle\Form\SequenceAlignmentType;
use MinitoolsBundle\Service\PcrAmplificationManager;
use MinitoolsBundle\Service\ProteinPropertiesManager;
use MinitoolsBundle\Service\ProteinToDnaManager;
use MinitoolsBundle\Service\RandomSequencesManager;
use MinitoolsBundle\Service\ReduceProteinAlphabetManager;
use MinitoolsBundle\Service\SequenceAlignmentManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use MinitoolsBundle\Entity\DnaToProtein;
use MinitoolsBundle\Entity\Protein;
use MinitoolsBundle\Form\ChaosGameRepresentationType;
use MinitoolsBundle\Form\ProteinPropertiesType;
use MinitoolsBundle\Form\DnaToProteinType;
use MinitoolsBundle\Form\DistanceAmongSequencesType;
use MinitoolsBundle\Form\FastaUploaderType;
use MinitoolsBundle\Form\FindPalindromesType;
use MinitoolsBundle\Form\MeltingTemperatureType;
use MinitoolsBundle\Form\MicroArrayDataAnalysisType;
use MinitoolsBundle\Form\MicrosatelliteRepeatsFinderType;
use MinitoolsBundle\Service\ChaosGameRepresentationManager;
use MinitoolsBundle\Service\DistanceAmongSequencesManager;
use MinitoolsBundle\Service\FastaUploaderManager;
use MinitoolsBundle\Service\FindPalindromeManager;
use MinitoolsBundle\Service\MeltingTemperatureManager;
use MinitoolsBundle\Service\MicroarrayAnalysisAdaptiveManager;
use MinitoolsBundle\Service\MicrosatelliteRepeatsFinderManager;
use MinitoolsBundle\Service\RestrictionDigestManager;

/**
 * Class MinitoolsController
 * @package MinitoolsBundle\Controller
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @todo : adding more listeners
 */
class MinitoolsController extends Controller
{
    use OligoTrait;

    private $dnaComplements;

    /**
     * MinitoolsController constructor.
     * @param Bioapi $bioapi
     */
    public function __construct(Bioapi $bioapi)
    {
        $this->dnaComplements = $bioapi->getDNAComplement();
    }

    /**
     * @Route("/minitools/chaos-game-representation/{schema}", name="chaos_game_representation")
     * @param   string                          $schema
     * @param   Request                         $request
     * @param   ChaosGameRepresentationManager  $chaosGameReprentationManager
     * @param   OligosManager                   $oligosManager
     * @return  Response
     * @throws  \Exception
     */
    public function chaosGameRepresentationAction(
        $schema,
        Request $request,
        ChaosGameRepresentationManager $chaosGameReprentationManager,
        OligosManager $oligosManager
    )
    {
        $form = $this->get('form.factory')->create(ChaosGameRepresentationType::class);

        if ($schema == "FCGR") {
            return $this->fcgrCompute($request, $chaosGameReprentationManager, $form, $oligosManager);
        }

        if ($schema == "CGR") {
            return $this->cgrCompute($request, $chaosGameReprentationManager, $form);
        }
    }


    /**
     * @param Request $request
     * @param ChaosGameRepresentationManager $chaosGameReprentationManager
     * @param $form
     * @return Response
     * @throws \Exception
     */
    public function cgrCompute(Request $request, ChaosGameRepresentationManager $chaosGameReprentationManager,
                               $form)
    {
        $isComputed = false;

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();
            $chaosGameReprentationManager->CGRCompute($formData["seq_name"], $formData["seq"], $formData["size"]);
            $isComputed = true;
        }

        return $this->render(
            '@Minitools/Minitools/chaosGameRepresentationCGR.html.twig',
            [
                'form'              => $form->createView(),
                'is_computed'       => $isComputed
            ]
        );
    }


    /**
     * @param Request $request
     * @param ChaosGameRepresentationManager $chaosGameReprentationManager
     * @param $form
     * @param $oligosManager
     * @return Response
     * @throws \Exception
     */
    public function fcgrCompute(Request $request,
                                ChaosGameRepresentationManager $chaosGameReprentationManager,
                                $form, $oligosManager)
    {
        $aOligos = null;
        $for_map = null;
        $aNucleotides = [];

        $isMap = false;
        $isFreq = false;

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();

            $aSeqData = $chaosGameReprentationManager->FCGRCompute(
                $formData["seq"],
                $formData["len"],
                $formData["s"],
                $this->dnaComplements
            );

            // compute nucleotide frequencies
            foreach($this->dnaComplements as $sNucleotide) {
                $aNucleotides[$sNucleotide] = substr_count($aSeqData["sequence"], $sNucleotide);
            }

            // COMPUTE OLIGONUCLEOTIDE FREQUENCIES
            //      frequencies are saved to an array named $aOligos
            $aOligos = $oligosManager->findOligos(
                $aSeqData["sequence"],
                $aSeqData["length"],
                array_values($this->dnaComplements)
            );

            // CREATE CHAOS GAME REPRESENTATION OF FREQUENCIES IMAGE
            //      check the function for more info on parameters
            //      $data contains a string with the data to be used to create the image map
            $for_map = $chaosGameReprentationManager->createFCGRImage(
                $aOligos,
                $formData["seq_name"],
                $aNucleotides,
                strlen($formData["seq"]),
                $formData["s"],
                $formData["len"]
            );

            $isMap = $formData["map"];
            $isFreq = $formData["freq"];

        }

        return $this->render(
            '@Minitools/Minitools/chaosGameRepresentationFCGR.html.twig',
            [
                'form'              => $form->createView(),
                'oligos'            => $aOligos,
                'areas'             => $for_map,
                'is_map'            => $isMap,
                'show_as_freq'      => $isFreq
            ]
        );
    }

    /**
     * @Route("/minitools/distance-among-sequences", name="distance_among_sequences")
     * @param   Request                         $request
     * @param   DistanceAmongSequencesManager   $oDistanceAmongSequencesManager
     * @return  Response
     * @throws \Exception
     */
    public function distanceAmongSequencesAction(
        Request $request,
        DistanceAmongSequencesManager $oDistanceAmongSequencesManager
    )
    {
        $form = $this->get('form.factory')->create(DistanceAmongSequencesType::class);

        $oligo_array = $data = [];
        $textcluster = $seq_name = $dendogramFile = "";
        $length = 0;

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();
            $length = $formData["len"];
            $seqs = $oDistanceAmongSequencesManager->formatSequences($formData["seq"]);

            // at this moment two arrays are available: $seqs (with sequences) and $seq_names (with name of sequences)
            if ($formData["method"] == "euclidean") { // EUCLIDEAN DISTANCE
                $oligo_array = $oDistanceAmongSequencesManager->computeOligonucleotidsFrequenciesEuclidean($seqs, $length, $this->dnaComplements);
                $data = $oDistanceAmongSequencesManager->computeDistancesAmongFrequenciesEuclidean($seqs, $oligo_array,$length);
            } else {
                $oligo_array = $oDistanceAmongSequencesManager->computeOligonucleotidsFrequencies($seqs, $this->dnaComplements);
                $data = $oDistanceAmongSequencesManager->computeDistancesAmongFrequencies($seqs, $oligo_array);
            }

            $dendogramFile = $this->getParameter('nucleotids_graphs')['dendogram_file'];
            $oDistanceAmongSequencesManager->upgmaClustering($data, $formData["method"], $length, $dendogramFile);
        }
        
        return $this->render(
            '@Minitools/Minitools/distanceAmongSequences.html.twig',
            [
                'form'              => $form->createView(),
                'oligo_array'       => $oligo_array,
                'data'              => $data,
                'length'            => $length,
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
     * @param       Request                 $request
     * @param       FindPalindromeManager   $oFindPalindromeManager
     * @return      Response
     * @throws      \Exception
     */
    public function findPalindromesAction(Request $request, FindPalindromeManager $oFindPalindromeManager)
    {
        $aPalindromes = [];
        $form = $this->get('form.factory')->create(FindPalindromesType::class);
        $min = 0;
        $max = 0;

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();

            $aPalindromes = $oFindPalindromeManager->findPalindromicSeqs(
                $formData["seq"],
                $formData["min"],
                $formData["max"]
            );
            $min = $formData["min"];
            $max = $formData["max"];
        }

        return $this->render(
            '@Minitools/Minitools/findPalindromes.html.twig',
            [
                'form'          => $form->createView(),
                'min'           => $min,
                'max'           => $max,
                'palindromes'   => $aPalindromes
            ]
        );
    }

    /**
     * @Route("/minitools/fasta-uploader", name="gc_content_finder")
     * @param   Request                 $request
     * @param   FastaUploaderManager    $oFastaUploaderManager
     * @return  Response
     * @throws  \Exception
     */
    public function fastaUploaderAction(Request $request, FastaUploaderManager $oFastaUploaderManager)
    {
        $length = 0;
        $a = 0; $g = 0; $t = 0; $c = 0;

        $form = $this->get('form.factory')->create(FastaUploaderType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();

            $var = $oFastaUploaderManager->createFiles(
                $formData["fasta"],
                $this->getParameter('brochures_directory')
            );

            $oFastaUploaderManager->checkNucleotidSequence($var,$a,$g,$t,$c, strlen($var));
        }

        return $this->render(
            '@Minitools/Minitools/gcContentFinder.html.twig',
            [
                'form'      => $form->createView(),
                'length'    => $length,
                'a'         => $a,
                't'         => $t,
                'g'         => $g,
                'c'         => $c,
                'at'        => ($length != 0) ? (($a + $t) / $length) * 100 : null,
                'gc'        => ($length != 0) ? (($g + $c) / $length) * 100 : null
            ]
        );
    }


    /**
     * @Route("/minitools/melting-temperature", name="melting_temperature")
     * @param   Request                     $request
     * @param   MeltingTemperatureManager   $oMeltingTemperatureManager
     * @return  Response
     * @throws  \Exception
     */
    public function meltingTemperatureAction(
        Request $request,
        MeltingTemperatureManager $oMeltingTemperatureManager
    )
    {
        $iPrimer = $cg = $upper_mwt = $lower_mwt = $countATGC = $tmMin = $tmMax = 0;
        $aTmBaseStacking = [];
        $bBasic = $bNearestNeighbor = false;

        $form = $this->get('form.factory')->create(MeltingTemperatureType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();
            $iPrimer = $formData["primer"];
            $bNearestNeighbor = $formData["nearestNeighbor"];
            $bBasic = $formData["basic"];

            $cg = $oMeltingTemperatureManager->calculateCG($iPrimer);
            $oMeltingTemperatureManager->calculateMWT($upper_mwt, $lower_mwt, $iPrimer);
            $oMeltingTemperatureManager->basicCalculations($bBasic, $iPrimer, $countATGC, $tmMin, $tmMax);
            $oMeltingTemperatureManager->neighborCalculations(
                $bNearestNeighbor,$aTmBaseStacking, $iPrimer, $formData["cp"], $formData["cs"], $formData["cmg"]
            );
        }

        return $this->render(
            '@Minitools/Minitools/meltingTemperature.html.twig',
            [
                'form'              => $form->createView(),
                'primer'            => $iPrimer,
                'basic'             => $bBasic,
                'nearest_neighbor'  => $bNearestNeighbor,
                'upper_mwt'         => $upper_mwt,
                'lower_mwt'         => $lower_mwt,
                'countATGC'         => $countATGC,
                'tm_min'            => $tmMin,
                'tm_max'            => $tmMax,
                'cg'                => $cg,
                'tmBaseStacking'    => $aTmBaseStacking
            ]
        );
    }

    /**
     * @Route("/minitools/micro-array-analysis-adaptive-quantification", name="micro_array_analysis_adaptive_quantification")
     * @param       Request                             $request
     * @param       MicroarrayAnalysisAdaptiveManager   $oMicroarrayAnalysisAdaptiveManager
     * @return      Response
     * @throws      \Exception
     */
    public function microArrayAnalysisAdaptiveQuantificationAction(
        Request $request,
        MicroarrayAnalysisAdaptiveManager $oMicroarrayAnalysisAdaptiveManager
    )
    {
        $results = array();

        $form = $this->get('form.factory')->create(MicroArrayDataAnalysisType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();
            $results = $oMicroarrayAnalysisAdaptiveManager->processMicroarrayDataAdaptiveQuantificationMethod(
                $formData["data"]
            );
        }

        return $this->render(
            '@Minitools/Minitools/microArrayAnalysisAdaptiveQuantification.html.twig',
            [
                'form'              => $form->createView(),
                'results'           => $results
            ]
        );
    }

    /**
     * @Route("/minitools/microsatellite-repeats-finder", name="microsatellite_repeats_finder")
     * @param       Request                             $request
     * @param       MicrosatelliteRepeatsFinderManager  $oMicrosatelliteRepeatsFinderManager
     * @return      Response
     * @throws      \Exception
     */
    public function microsatelliteRepeatsFinderAction (
        Request $request,
        MicrosatelliteRepeatsFinderManager $oMicrosatelliteRepeatsFinderManager
    ) {
        $results = [];

        $form = $this->get('form.factory')->create(MicrosatelliteRepeatsFinderType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();

            $results = $oMicrosatelliteRepeatsFinderManager->findMicrosatelliteRepeats(
                $formData["sequence"],
                $formData["min"],
                $formData["max"],
                $formData["min_repeats"],
                $formData["length_of_MR"],
                $formData["mismatch"]
            );
        }

        return $this->render(
            '@Minitools/Minitools/microsatelliteRepeatsFinder.html.twig',
            [
                'form'              => $form->createView(),
                'results'           => $results
            ]
        );
    }

    /**
     * @Route("/minitools/oligonucleotide-frequency", name="oligonucleotide_frequency")
     * @param   Request         $request
     * @param   OligosManager   $oligosManager
     * @return  Response
     * @throws  \Exception
     */
    public function oligonucleotideFrequencyAction(Request $request, OligosManager $oligosManager)
    {
        $aResults = [];
        $iLength = 0;

        $form = $this->get('form.factory')->create(OligoNucleotideFrequencyType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();
            $iLength = $formData["len"];
            $sSequence = $formData["sequence"];

            // when frequencies at both strands are requested, place sequence and reverse complement of sequence in one line
            if ($formData["strands"] == 2) {
                $this->createInversion($sSequence, $this->dnaComplements);
            }

            $aResults = $oligosManager->findOligos($sSequence, $iLength, $this->dnaComplements);
            ksort($aResults);
        }

        return $this->render(
            '@Minitools/Minitools/oligonucleotideFrequency.html.twig',
            [
                'form'              => $form->createView(),
                'results'           => $aResults,
                'length'            => $iLength
            ]
        );
    }

    /**
     * @Route("/minitools/pcr-amplification", name="pcr_amplification")
     * @param       Request                     $request
     * @param       PcrAmplificationManager     $pcrAmplificationManager
     * @return      Response
     * @throws      \Exception
     */
    public function pcrAmplificationAction(Request $request, PcrAmplificationManager $pcrAmplificationManager)
    {
        $aResults = [];
        $primer1 = $primer2 = null;
        $sSequence = "";

        $form = $this->get('form.factory')->create(PcrAmplificationType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();
            $primer1 = $formData["primer1"];
            $primer2 = $formData["primer2"];
            $sSequence = $formData["sequence"];

            $sStartPattern = $pcrAmplificationManager->createStartPattern(
                $primer1,
                $primer2,
                $formData["allowmismatch"]
            );

            $sEndPattern = $pcrAmplificationManager->createEndPattern($sStartPattern);

            $aResults = $pcrAmplificationManager->amplify(
                $sStartPattern,
                $sEndPattern,
                $sSequence,
                $formData["length"]
            );
        }

        return $this->render(
            '@Minitools/Minitools/pcrAmplification.html.twig',
            [
                'form'         => $form->createView(),
                'results'      => $aResults,
                'sequence'     => $sSequence,
                'primer1'      => $primer1,
                'primer2'      => $primer2
            ]
        );
    }

    /**
     * @Route("/minitools/protein-properties", name="protein_properties")
     * @param       Request                     $request
     * @param       ProteinPropertiesManager    $proteinPropertiesManager
     * @return      Response
     * @throws      \Exception
     * @TODO : Molar absorbsion et isoelectric point ne correspondent pas
     */
    public function proteinPropertiesAction(
        Request $request,
        ProteinPropertiesManager $proteinPropertiesManager,
        Bioapi $bioapi
    ){
        $data_source = $three_letter_code = $subsequence    =  "";
        $molweight = $abscoef = $charge = $charge2          = 0;
        $aminoacids = $colored_seq = $colored_seq2          = [];
        $results                                            = false;

        $form = $this->get('form.factory')->create(ProteinPropertiesType::class);

        $colors = $this->getParameter('analysis_color');

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData       = $form->getData();
            $data_source    = $formData["data_source"];
            $pH             = $formData["pH"];
            $results        = true;

            // remove non coding (works by default)
            $seq = $this->removeNonCodingProt($formData["seq"]);
            $subsequence = $proteinPropertiesManager -> writeSubsequence($formData["start"], $formData["end"], $seq);
            // calculate nucleotide composition
            $aminoacid_content = $proteinPropertiesManager->aminoacidContent($seq);
            // get pk values for charged aminoacids
            $pK = $bioapi->getPkValueById($formData["data_source"]);

            // prepare nucleotide composition to be printed out
            if ((bool)$formData["composition"]) {
                $aminoacids = $proteinPropertiesManager->formatAminoacidContent($aminoacid_content);
            }
            if ((bool)$formData["molweight"]) {
                 $molweight = $proteinPropertiesManager->proteinMolecularWeight($aminoacid_content);
            }
            if ((bool)$formData["abscoef"]) {
                $abscoef = $proteinPropertiesManager->molarAbsorptionCoefficientOfProt($aminoacid_content, $molweight);
            }
            if ((bool)$formData["charge"]) {  // calculate isoelectric point of protein
                $charge = $proteinPropertiesManager->proteinIsoelectricPoint($pK, $aminoacid_content);
            }
            if ((bool)$formData["charge2"]) {   // calculate charge of protein at requested pH
                $charge2 = $proteinPropertiesManager->proteinCharge($pK, $aminoacid_content, $pH);
            }
            if ((bool)$formData["three_letters"]) {  // colored sequence based in plar/non-plar/charged aminoacids
                foreach(str_split($seq) as $letter) {
                    $three_letter_code .= $proteinPropertiesManager->seq1letterTo3letter($letter);
                }
            }
            if ((bool)$formData["type1"]) {   // colored sequence based in polar/non-plar/charged aminoacids
                $colored_seq = $proteinPropertiesManager->proteinAminoacidNature1($seq, $colors);
            }
            if ((bool)$formData["type2"]) {
                $colored_seq2 = $proteinPropertiesManager->proteinAminoacidNature2($seq, $colors);
            }
        }

        return $this->render(
            '@Minitools/Minitools/proteinProperties.html.twig',
            [
                'form'                  => $form->createView(),
                'subsequence'           => $subsequence,
                'aminoacids'            => $aminoacids,
                'molweight'             => $molweight,
                'abscoef'               => $abscoef,
                'data_source'           => $data_source,
                'pH'                    => $pH,
                'charge'                => $charge,
                'charge2'               => $charge2,
                'three_letter_code'     => $three_letter_code,
                'colored_seq'           => $colored_seq,
                'colored_seq2'          => $colored_seq2,
                'colors'                => $colors,
                'results'               => $results
            ]
        );
    }

    /**
     * @Route("/minitools/protein-to-dna", name="protein_to_dna")
     * @param       Request                 $request
     * @param       ProteinToDnaManager     $proteinToDnaManager
     * @return      Response
     * @throws      \Exception
     */
    public function proteinToDnaAction(Request $request, ProteinToDnaManager $proteinToDnaManager)
    {
        $oProteinToDna  = new ProteinToDna();
        $dna            = "";

        $form = $this->get('form.factory')->create(ProteinToDnaType::class, $oProteinToDna);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $dna = $proteinToDnaManager->translateProteinToDNA(
                $oProteinToDna->getSequence(),
                $oProteinToDna->getGeneticCode()
            );
        }

        return $this->render(
            '@Minitools/Minitools/proteinToDna.html.twig',
            [
                'form'                  => $form->createView(),
                'sequence'              => $oProteinToDna->getSequence(),
                'dna'                   => $dna
            ]
        );
    }

    /**
     * @Route("/minitools/random-seqs", name="random_seqs")
     * @param       Request                     $request
     * @param       RandomSequencesManager      $randomSequencesManager
     * @return      Response
     * @throws      \Exception
     */
    public function randomSeqsAction(Request $request, RandomSequencesManager $randomSequencesManager)
    {
        $oRandomSequence    = new RandomSequences();
        $result             = "";
        $aAminoAcids        = [];

        $form = $this->get('form.factory')->create(RandomSequencesType::class, $oRandomSequence);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            switch($oRandomSequence->getProcedure()) {
                case "fromseq":
                    $length1 = $oRandomSequence->getLength1();
                    $result = $randomSequencesManager->createFromSeq($oRandomSequence->getSeq(), $length1);
                    break;
                case "fromACGT":
                    $length2 = $oRandomSequence->getLength2();
                    $aAminoAcids["A"] = $oRandomSequence->getDnaA();
                    $aAminoAcids["C"] = $oRandomSequence->getDnaC();
                    $aAminoAcids["G"] = $oRandomSequence->getDnaG();
                    $aAminoAcids["T"] = $oRandomSequence->getDnaT();
                    $result = $randomSequencesManager->createFromACGT($aAminoAcids, $length2);
                    break;
                case "fromAA":
                    $length3 = $oRandomSequence->getLength3();
                    $aAminoAcids["A"] = $oRandomSequence->getA();
                    $aAminoAcids["C"] = $oRandomSequence->getC();
                    $aAminoAcids["D"] = $oRandomSequence->getD();
                    $aAminoAcids["E"] = $oRandomSequence->getE();
                    $aAminoAcids["F"] = $oRandomSequence->getF();
                    $aAminoAcids["G"] = $oRandomSequence->getG();
                    $aAminoAcids["H"] = $oRandomSequence->getH();
                    $aAminoAcids["I"] = $oRandomSequence->getI();
                    $aAminoAcids["K"] = $oRandomSequence->getK();
                    $aAminoAcids["L"] = $oRandomSequence->getL();
                    $aAminoAcids["M"] = $oRandomSequence->getM();
                    $aAminoAcids["N"] = $oRandomSequence->getN();
                    $aAminoAcids["P"] = $oRandomSequence->getP();
                    $aAminoAcids["Q"] = $oRandomSequence->getQ();
                    $aAminoAcids["R"] = $oRandomSequence->getR();
                    $aAminoAcids["S"] = $oRandomSequence->getS();
                    $aAminoAcids["T"] = $oRandomSequence->getT();
                    $aAminoAcids["V"] = $oRandomSequence->getV();
                    $aAminoAcids["W"] = $oRandomSequence->getW();
                    $aAminoAcids["Y"] = $oRandomSequence->getY();
                    $result = $randomSequencesManager->createFromAA($aAminoAcids, $length3);
            }
        }

        return $this->render(
            '@Minitools/Minitools/randomSeqs.html.twig',
            [
                'form'         => $form->createView(),
                'results'      => $result
            ]
        );
    }


    /**
     * @Route("/minitools/reduce-protein-alphabet", name="reduce_protein_alphabet")
     * @param       Request                         $request
     * @param       ReduceProteinAlphabetManager    $reduceProteinAlphabetManager
     * @return      Response
     * @throws      \Exception
     */
    public function reduceProteinAlphabetAction(Request $request, ReduceProteinAlphabetManager $reduceProteinAlphabetManager)
    {
        $oReduceAlphabet = new ReduceAlphabet();
        $form = $this->get('form.factory')->create(ReduceAlphabetType::class, $oReduceAlphabet);
        $reducedCode = "";
        $reducedSeq = "";

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // REDUCE ALPHABET
            if ($oReduceAlphabet->getMode() == "pre") {
                // for predefined reduced alphabets
                if ($oReduceAlphabet->getSeq() != ""
                    && $oReduceAlphabet->getType() != ""
                    && $oReduceAlphabet->getAaperline() != "") {
                    $reducedSeq = $reduceProteinAlphabetManager->reduceAlphabet(
                        $oReduceAlphabet->getSeq(),
                        $oReduceAlphabet->getType()
                    );

                    $reducedCode = $this->getParameter('types_infos')[$oReduceAlphabet->getType()];
                }
            } else {
                // for personalized reduced alphabets
                if ($oReduceAlphabet->getSeq() != "" && $oReduceAlphabet->getAaperline() != "") {
                    $reducedSeq =  $reduceProteinAlphabetManager->reduceAlphabetCustom(
                        $oReduceAlphabet->getSeq(),
                        $oReduceAlphabet->getCustomAlphabet()
                    );
                }
            }
        }

        return $this->render(
            '@Minitools/Minitools/reduceProteinAlphabet.html.twig',
            [
                'form'                  => $form->createView(),
                'reduced_code'          => $reducedCode,
                'reduced_seq'           => $reducedSeq,
                'mode'                  => $oReduceAlphabet->getMode(),
                'custom_alphabet'       => $oReduceAlphabet->getCustomAlphabet(),
                'sequence'              => $oReduceAlphabet->getSeq(),
                'show_reduced'          => $oReduceAlphabet->isShowReduced(),
                'type'                  => $oReduceAlphabet->getType(),
                'aa_perline'            => $oReduceAlphabet->getAaperline()
            ]
        );
    }

    /**
     * @Route("/minitools/restriction-digest", name="restriction_digest")
     * @param       Request                     $request
     * @param       RestrictionDigestManager    $restrictionDigestManager
     * @return      Response
     * @throws      \Exception
     */
    public function restrictionDigestAction(Request $request, RestrictionDigestManager $restrictionDigestManager)
    {
        $sequence = "";
        $digestion = [];
        $enzymes_array = [];
        $digestionMulti = [];

        $oRestrictionEnzimeDigest = new RestrictionEnzymeDigest();
        $form = $this->get('form.factory')->create(RestrictionEnzymeDigestType::class, $oRestrictionEnzimeDigest);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $sequence = $restrictionDigestManager->extractSequences($oRestrictionEnzimeDigest->getSequence());

            // We will get info for endonucleases. The info is included within 3 different functions in the bottom (for Type II, IIb and IIs enzymes)
            // Type II endonucleases are always used
            $enzymes_array = $this->getParameter('enzymes')['typeII_endonucleases'];

            // if TypeIIs endonucleases are requested, get them
            if (($oRestrictionEnzimeDigest->isIIs() && !$oRestrictionEnzimeDigest->isDefined())) {
                $enzymes_array = array_merge($enzymes_array, $this->getParameter('enzymes')['typeIIs_endonucleases']);
                asort($enzymes_array);
            }
            // if TypeIIb endonucleases are requested, get them
            if (($oRestrictionEnzimeDigest->isIIb() && !$oRestrictionEnzimeDigest->isDefined())) {
                $enzymes_array = array_merge($enzymes_array, $this->getParameter('enzymes')['typeIIb_endonucleases']);
                asort($enzymes_array);
            }

            $enzymes_array = $restrictionDigestManager->reduceEnzymesArray(
                $enzymes_array,
                $oRestrictionEnzimeDigest->getMinimum(),
                $oRestrictionEnzimeDigest->getRetype(),
                $oRestrictionEnzimeDigest->isDefined(),
                $oRestrictionEnzimeDigest->getWre()
            );

            // RESTRICTION DIGEST OF SEQUENCE
            foreach($sequence as $number => $val) {
                $digestion[$number] = $restrictionDigestManager->restrictionDigest($enzymes_array, $sequence[$number]["seq"]);
            }

            if (sizeof($sequence) > 1) {
                $digestionMulti = $restrictionDigestManager->enzymesForMultiSeq(
                    $sequence,
                    $digestion,
                    $enzymes_array,
                    $oRestrictionEnzimeDigest->isOnlydiff(),
                    $oRestrictionEnzimeDigest->getWre()
                );
            }
        }

        return $this->render(
            '@Minitools/Minitools/restrictionDigest.html.twig',
            [
                'form'              => $form->createView(),
                'show_code'         => $oRestrictionEnzimeDigest->isShowcode(),
                'sequence'          => $sequence,
                'digestion'         => $digestion,
                'digestion_multi'   => array_unique($digestionMulti),
                'enzymes_array'     => $enzymes_array
            ]
        );
    }

    /**
     * @Route("/minitools/show-vendors/{enzyme}", name="show_vendors")
     * @param   string                      $enzyme
     * @param   RestrictionDigestManager    $restrictionDigestManager
     * @return  JsonResponse
     * @throws  \Exception
     */
    public function showVendorsAction($enzyme, RestrictionDigestManager $restrictionDigestManager)
    {
        $message = "";
        $enzyme_array = [];
        // Get array of companies selling each endonuclease
        $vendors = $this->getParameter('vendors');

        $endonuclease = preg_split("/,/", $enzyme);
        if (strpos($enzyme,",") > 0) {
            $message = "All endonucleases bellow are isoschizomers";
        }

        // print vendor for each endonuclease (uses a function)
        foreach ($endonuclease as $enzyme) {
            $enzyme_array[$enzyme] = $restrictionDigestManager->showVendors($vendors[$enzyme], $enzyme);
        }

        return new JsonResponse(["message" => $message, "vendors" => $enzyme_array]);
    }

    /**
     * @Route("/minitools/seq-alignment", name="seq_alignment")
     * @param   Request $request
     * @param   SequenceAlignmentManager $sequenceAlignmentManager
     * @return  Response
     * @throws \Exception
     */
    public function seqAlignmentAction(Request $request, SequenceAlignmentManager $sequenceAlignmentManager)
    {
        $sequenceAlignment = new SequenceAlignment();
        $form = $this->get('form.factory')->create(SequenceAlignmentType::class, $sequenceAlignment);
        $sCompare = "";
        $sAlignSeqA = "";
        $sAlignSeqB = "";

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            /**
             * Limit sequence length to limit memory usage
             * This script creates a big array that requires a huge amount of memory
             * Do not use sequences longer than 700 bases each (1400 for both sequences)
             * In this demo, the limit has been set up to 300 bases.
             */
            $iLimit = 300;
            if ((strlen($sequenceAlignment->getSequence()) + strlen($sequenceAlignment->getSequence2())) > $iLimit) {
                throw new \Exception ("The maximum length of code accepted for both sequences is $iLimit nucleotides");
            }

            // CHECK WHETHER THEY ARE DNA OR PROTEIN, AND ALIGN SEQUENCES
            if ((substr_count($sequenceAlignment->getSequence(),"A")
                    + substr_count($sequenceAlignment->getSequence(),"C")
                    + substr_count($sequenceAlignment->getSequence(),"G")
                    + substr_count($sequenceAlignment->getSequence(),"T")
                ) > (strlen($sequenceAlignment->getSequence()) / 2)) {
                // if A+C+G+T is at least half of the sequence, it is a DNA
                $aAlignment = $sequenceAlignmentManager->alignDNA(
                    $sequenceAlignment->getSequence(),
                    $sequenceAlignment->getSequence2()
                );
            } else {
                // else is protein
                $aAlignment = $sequenceAlignmentManager->alignProteins(
                    $sequenceAlignment->getSequence(),
                    $sequenceAlignment->getSequence2()
                );
            }

            // EXTRACT DATA FROM ALIGNMENT
            $sAlignSeqA = $aAlignment["seqa"];
            $sAlignSeqB = $aAlignment["seqb"];

            // COMPARE ALIGNMENTS
            $sCompare = $sequenceAlignmentManager->compareAlignment($sAlignSeqA, $sAlignSeqB);
        }

        return $this->render(
            '@Minitools/Minitools/seqAlignment.html.twig',
            [
                'form'          => $form->createView(),
                'compare'       => $sCompare,
                'align_seqa'    => $sAlignSeqA,
                'align_seqb'    => $sAlignSeqB,
                'sequence1'     => $sequenceAlignment->getId1(),
                'sequence2'     => $sequenceAlignment->getId2(),
            ]
        );
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