<?php
/**
 * Minitools controller
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 february 2019
 * Last modified 26 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Controller;

use AppBundle\Entity\Fasta;
use AppBundle\Service\NucleotidsManager;
use AppBundle\Service\OligosManager;

use MinitoolsBundle\Entity\OligoNucleotideFrequency;
use MinitoolsBundle\Entity\PcrAmplification;
use MinitoolsBundle\Form\OligoNucleotideFrequencyType;
use MinitoolsBundle\Form\PcrAmplificationType;
use MinitoolsBundle\Service\PcrAmplificationManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use MinitoolsBundle\Entity\ChaosGameRepresentation;
use MinitoolsBundle\Entity\DnaToProtein;
use MinitoolsBundle\Entity\Protein;
use MinitoolsBundle\Form\ChaosGameRepresentationType;
use MinitoolsBundle\Form\ProteinPropertiesType;
use MinitoolsBundle\Form\DnaToProteinType;
use MinitoolsBundle\Entity\DistanceAmongSequences;
use MinitoolsBundle\Entity\FastaUploader;
use MinitoolsBundle\Entity\FindPalindromes;
use MinitoolsBundle\Entity\MeltingTemperature;
use MinitoolsBundle\Entity\MicroArrayDataAnalysis;
use MinitoolsBundle\Entity\MicrosatelliteRepeatsFinder;
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

/**
 * Class MinitoolsController
 * @package MinitoolsBundle\Controller
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MinitoolsController extends Controller
{
    /**
     * @Route("/minitools/chaos-game-representation/{schema}", name="chaos_game_representation")
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

        $oFastaUploader = new FastaUploader();

        $form = $this->get('form.factory')->create(FastaUploaderType::class, $oFastaUploader);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $file = $oFastaUploader->getFasta();
            $fileName = md5(uniqid()).'.txt';

            try {
                $file->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                throw new FileException($e);
            }

            $myFile = $this->getParameter('brochures_directory').'/'.$fileName;
            $fh = fopen($myFile, 'r');
            $var = fread($fh, 1000000);
            fclose($fh);

            $length = strlen($var);
            if($length != '') {
                if($oFastaUploaderManager->isValidSequence($var)) {
                    for ($i = 0; $i < $length ; ++$i) {
                        switch($var[$i]) {
                            case 'a':
                            case 'A':
                                $a++;
                                break;
                            case 't':
                            case 'T':
                                $t++;
                                break;
                            case 'c':
                            case 'C':
                                $c++;
                                break;
                            case 'g':
                            case 'G':
                                $g++;
                        }
                    }
                } else {
                    throw new \Exception("Please check....This is not a Nucleotide sequence");
                }
            }
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
     * @param   Request $request
     * @param   MeltingTemperatureManager $oMeltingTemperatureManager
     * @param   NucleotidsManager $oNucleotidsManager
     * @return  Response
     * @throws  \Exception
     */
    public function meltingTemperatureAction(
        Request $request,
        MeltingTemperatureManager $oMeltingTemperatureManager,
        NucleotidsManager $oNucleotidsManager
    )
    {
        $cg = 0;
        $upper_mwt = $lower_mwt = 0;
        $countATGC = 0;
        $tmMin = $tmMax = 0;
        $aTmBaseStacking = [];
        $oMeltingTemperature = new MeltingTemperature();

        $form = $this->get('form.factory')->create(MeltingTemperatureType::class, $oMeltingTemperature);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $cg = round(100 * $oNucleotidsManager->countCG($oMeltingTemperature->getPrimer())
                / strlen($oMeltingTemperature->getPrimer()),1);

            $upper_mwt = $oMeltingTemperatureManager->molwt($oMeltingTemperature->getPrimer(),"DNA","upperlimit");
            $lower_mwt = $oMeltingTemperatureManager->molwt($oMeltingTemperature->getPrimer(),"DNA","lowerlimit");

            if($oMeltingTemperature->isBasic()) {
                $countATGC = $oNucleotidsManager->countACGT($oMeltingTemperature->getPrimer());
                $tmMin = $oMeltingTemperatureManager->tmMin($oMeltingTemperature->getPrimer());
                $tmMax = $oMeltingTemperatureManager->tmMax($oMeltingTemperature->getPrimer());
            }

            if($oMeltingTemperature->isNearestNeighbor()) {
                $aTmBaseStacking = $oMeltingTemperatureManager->tmBaseStacking(
                    $oMeltingTemperature->getPrimer(),
                    $oMeltingTemperature->getCp(),
                    $oMeltingTemperature->getCs(),
                    $oMeltingTemperature->getCmg()
                );
            }
        }

        return $this->render(
            '@Minitools/Minitools/meltingTemperature.html.twig',
            [
                'form'              => $form->createView(),
                'primer'            => $oMeltingTemperature->getPrimer(),
                'basic'             => $oMeltingTemperature->isBasic(),
                'nearest_neighbor'  => $oMeltingTemperature->isNearestNeighbor(),
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
     * @param Request $request
     * @param MicroarrayAnalysisAdaptiveManager $oMicroarrayAnalysisAdaptiveManager
     * @return Response
     * @throws \Exception
     */
    public function microArrayAnalysisAdaptiveQuantificationAction(
        Request $request,
        MicroarrayAnalysisAdaptiveManager $oMicroarrayAnalysisAdaptiveManager
    )
    {
        $oMicroArrayDataAnalysis = new MicroArrayDataAnalysis();
        $results = array();

        $form = $this->get('form.factory')->create(MicroArrayDataAnalysisType::class, $oMicroArrayDataAnalysis);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $results = $oMicroarrayAnalysisAdaptiveManager->processMicroarrayDataAdaptiveQuantificationMethod(
                $oMicroArrayDataAnalysis->getData()
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
     * @param Request $request
     * @param MicrosatelliteRepeatsFinderManager $oMicrosatelliteRepeatsFinderManager
     * @return Response
     * @throws \Exception
     */
    public function microsatelliteRepeatsFinderAction (
        Request $request,
        MicrosatelliteRepeatsFinderManager $oMicrosatelliteRepeatsFinderManager
    ) {
        $results = [];
        $oMicrosatelliteRepeatsFinder = new MicrosatelliteRepeatsFinder();

        $form = $this->get('form.factory')->create(
            MicrosatelliteRepeatsFinderType::class,
            $oMicrosatelliteRepeatsFinder
        );

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $sequence = strtoupper($oMicrosatelliteRepeatsFinder->getSequence()); // Get the sequence
            $sequence = preg_replace("/\\W|\\d/","",$sequence); // Remove non word and digits from sequence

            $results = $oMicrosatelliteRepeatsFinderManager->findMicrosatelliteRepeats(
                $sequence,
                $oMicrosatelliteRepeatsFinder->getMin(),
                $oMicrosatelliteRepeatsFinder->getMax(),
                $oMicrosatelliteRepeatsFinder->getMinRepeats(),
                $oMicrosatelliteRepeatsFinder->getLengthOfMR(),
                $oMicrosatelliteRepeatsFinder->getMismatch()
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
        $oOligoNucleotideFrequency = new OligoNucleotideFrequency();
        $aResults = [];

        $form = $this->get('form.factory')->create(
            OligoNucleotideFrequencyType::class,
            $oOligoNucleotideFrequency
        );

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // remove useless from sequence (non-letters and digists are removed)
            $sSequence = preg_replace("/\W|\d/","", $oOligoNucleotideFrequency->getSequence());  // removed

            // when length of query sequence is bellow 4^oligo_len => error (to avoid a lot of 0 frequencies);
            if (strlen($sSequence) < pow(4, $oOligoNucleotideFrequency->getLen())) {
                throw new \Exception("Query sequence must be at least 4^(length of oligo) to proceed.");
            }

            // when frequencies at both strands are requested, place sequence and reverse complement of sequence in one line
            if ($oOligoNucleotideFrequency->getStrands() == 2) {
                $seqRevert = strrev($sSequence);
                foreach ($this->getParameter('dna_complements') as $nucleotide => $complement) {
                    $seqRevert = str_replace($nucleotide, strtolower($complement), $seqRevert);
                }
                $sSequence .= " ".strtoupper($seqRevert);
            }

            $aResults = $oligosManager->findOligos(
                $sSequence,
                $oOligoNucleotideFrequency->getLen(),
                $this->getParameter('dna_complements')
            );

            ksort($aResults);
        }

        return $this->render(
            '@Minitools/Minitools/oligonucleotideFrequency.html.twig',
            [
                'form'              => $form->createView(),
                'results'           => $aResults,
                'length'            => $oOligoNucleotideFrequency->getLen()
            ]
        );
    }

    /**
     * @Route("/minitools/pcr-amplification", name="pcr_amplification")
     * @param Request $request
     * @param PcrAmplificationManager $pcrAmplificationManager
     * @return Response
     * @throws \Exception
     */
    public function pcrAmplificationAction(Request $request, PcrAmplificationManager $pcrAmplificationManager)
    {
        $pcrAmplification = new PcrAmplification();
        $aResults = [];

        $form = $this->get('form.factory')->create(
            PcrAmplificationType::class,
            $pcrAmplification
        );

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            // SET PATTERNS FROM PRIMERS
            // Change N to point in primers
            $sPattern1 = str_replace("N", ".", $pcrAmplification->getPrimer1());
            $sPattern2 = str_replace("N", ".", $pcrAmplification->getPrimer2());


            if ($pcrAmplification->isAllowmismatch()) {
                $sPattern1 = $pcrAmplificationManager->includeN($pcrAmplification->getPrimer1());
                $sPattern2 = $pcrAmplificationManager->includeN($pcrAmplification->getPrimer2());
            }

            $sStartPattern = "$sPattern1|$sPattern2"; // SET PATTERN

            $seqRevert = strrev($sStartPattern);
            foreach ($this->getParameter('dna_complements') as $nucleotide => $complement) {
                $seqRevert = str_replace($nucleotide, strtolower($complement), $seqRevert);
            }
            $sEndPattern = strtoupper($seqRevert);

            $aResults = $pcrAmplificationManager->amplify(
                $sStartPattern,
                $sEndPattern,
                $pcrAmplification->getSequence(),
                $pcrAmplification->getLength()
            );
        }

        return $this->render(
            '@Minitools/Minitools/pcrAmplification.html.twig',
            [
                'form'         => $form->createView(),
                'results'      => $aResults,
                'sequence'     => $pcrAmplification->getSequence(),
                'primer1'      => $pcrAmplification->getPrimer1(),
                'primer2'      => $pcrAmplification->getPrimer2()
            ]
        );
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