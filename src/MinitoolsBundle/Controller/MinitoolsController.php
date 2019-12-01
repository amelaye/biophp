<?php
/**
 * Minitools controller
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 february 2019
 * Last modified 27 august 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Controller;

use AppBundle\Service\Misc\GeneticsFunctions;
use MinitoolsBundle\Form\SequenceManipulationType;
use MinitoolsBundle\Form\SkewsType;
use MinitoolsBundle\Service\SequenceManipulationAndDataManager;
use MinitoolsBundle\Service\SkewsManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Service\Misc\OligosManager;
use AppBundle\Traits\OligoTrait;

use MinitoolsBundle\Form\OligoNucleotideFrequencyType;
use MinitoolsBundle\Form\PcrAmplificationType;
use MinitoolsBundle\Form\RestrictionEnzymeDigestType;
use MinitoolsBundle\Form\FastaUploaderType;
use MinitoolsBundle\Form\MeltingTemperatureType;
use MinitoolsBundle\Form\MicroArrayDataAnalysisType;
use MinitoolsBundle\Form\MicrosatelliteRepeatsFinderType;
use MinitoolsBundle\Service\PcrAmplificationManager;
use MinitoolsBundle\Service\FastaUploaderManager;
use MinitoolsBundle\Service\MeltingTemperatureManager;
use MinitoolsBundle\Service\MicroarrayAnalysisAdaptiveManager;
use MinitoolsBundle\Service\MicrosatelliteRepeatsFinderManager;
use MinitoolsBundle\Service\RestrictionDigestManager;

/**
 * Class MinitoolsController
 * @package MinitoolsBundle\Controller
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MinitoolsController extends Controller
{
    use OligoTrait;

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
            'minitools/gcContentFinder.html.twig',
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
            'minitools/meltingTemperature.html.twig',
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
     * @Route("/minitools/micro-array-analysis-adaptive-quantification",
     *     name="micro_array_analysis_adaptive_quantification")
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
            'minitools/microArrayAnalysisAdaptiveQuantification.html.twig',
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
            'minitools/microsatelliteRepeatsFinder.html.twig',
            [
                'form'              => $form->createView(),
                'results'           => $results
            ]
        );
    }

    /**
     * @Route("/minitools/oligonucleotide-frequency", name="oligonucleotide_frequency")
     * @param   Request         $request
     * @return  Response
     * @throws  \Exception
     */
    public function oligonucleotideFrequencyAction(Request $request)
    {
        $aResults = [];
        $iLength = 0;

        $form = $this->get('form.factory')->create(OligoNucleotideFrequencyType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();
            $iLength = $formData["len"];
            $sSequence = $formData["sequence"];

            // when frequencies at both strands are requested,
            // place sequence and reverse complement of sequence in one line
            if ($formData["strands"] == 2) {
                GeneticsFunctions::createInversion($sSequence, OligosManager::GetDnaComplements());
            }

            $aResults = OligosManager::FindOligos($sSequence, $iLength);
            ksort($aResults);
        }

        return $this->render(
            'minitools/oligonucleotideFrequency.html.twig',
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
            'minitools/pcrAmplification.html.twig',
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
     * @Route("/minitools/restriction-digest", name="restriction_digest")
     * @param       Request                     $request
     * @param       RestrictionDigestManager    $restrictionDigestManager
     * @return      Response
     * @throws      \Exception
     */
    public function restrictionDigestAction(Request $request, RestrictionDigestManager $restrictionDigestManager)
    {
        $sequence  = "";
        $digestion = $enzymes_array = $enzymes_array = $digestionMulti = $digestionMulti = [];
        $bShowCode = false;

        $form = $this->get('form.factory')->create(RestrictionEnzymeDigestType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData       = $form->getData();

            $bShowCode = $formData["showcode"];
            $sequence = $restrictionDigestManager->extractSequences($formData["sequence"]);

            $enzymes_array = $restrictionDigestManager->getNucleolasesInfos(
                $formData["IIs"],
                $formData["IIb"],
                $formData["defined"]
            );

            $enzymes_array = $restrictionDigestManager->reduceEnzymesArray(
                $enzymes_array,
                $formData["minimum"],
                $formData["retype"],
                $formData["defined"],
                $formData["wre"]
            );

            // RESTRICTION DIGEST OF SEQUENCE
            foreach($sequence as $number => $val) {
                $digestion[$number] = $restrictionDigestManager->restrictionDigest(
                    $enzymes_array,
                    $sequence[$number]["seq"]
                );
            }

            if (sizeof($sequence) > 1) {
                $digestionMulti = $restrictionDigestManager->enzymesForMultiSeq(
                    $sequence,
                    $digestion,
                    $enzymes_array,
                    $formData["onlydiff"],
                    $formData["wre"]
                );
            }
        }

        return $this->render(
            'minitools/restrictionDigest.html.twig',
            [
                'form'              => $form->createView(),
                'show_code'         => $bShowCode,
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
        $enzyme_array = $restrictionDigestManager->getVendors($message, $enzyme);

        return new JsonResponse(["message" => $message, "vendors" => $enzyme_array]);
    }


    /**
     * @Route("/minitools/sequences-manipulation-and-data", name="sequences_manipulation_and_data")
     * @param   Request $request
     * @param   SequenceManipulationAndDataManager  $sequenceManipulationAndDataManager
     * @return  Response
     * @throws  \Exception
     */
    public function sequencesManipulationAndDataAction(
        Request $request,
        SequenceManipulationAndDataManager $sequenceManipulationAndDataManager
    ) {
        $result = "";
        $form = $this->get('form.factory')->create(SequenceManipulationType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData       = $form->getData();
            $seq = $formData["seq"];

            // if subsequence is requested
            if (isset($formData["start"]) || isset($formData["end"])) {
                $start = $formData["start"] != "" ? ($formData["start"] - 1) : 0;
                $end = $formData["end"] != "" ? $formData["end"] : strlen($formData["seq"]);
                $seq = substr($formData["seq"], $start,$end - $start);
            }

            switch($formData["action"][0]) {
                case "reverse":
                    $result = strrev($seq); // reverse the sequence
                    break;
                case "complement":
                    // get the complementary sequence
                    $result = $sequenceManipulationAndDataManager->complement($seq);
                    break;
                case "reverse_and_complement":
                    $seq = strrev($formData["seq"]); // reverse the sequence
                    $result = $sequenceManipulationAndDataManager->complement($seq); // get the complementary sequence
                    break;
                case "display_both_strands":
                    // get a string with results
                    $result = $sequenceManipulationAndDataManager->displayBothStrands($seq);
                    break;
                case "toRNA":
                    $result = $sequenceManipulationAndDataManager->toRNA($seq); // get a string with results
                    break;
                default:
                    $result = $seq;
                    break;
            }

            if($formData["GC"]) {
                $result .= $sequenceManipulationAndDataManager->gcContent($seq); // calculate G+C content
            }
            if ($formData["ACGT"]) {
                // calculate nucleotide composition
                $result .= $sequenceManipulationAndDataManager->acgtContent($seq);
            }
        }

        return $this->render(
            'minitools/sequencesManipulationAndData.html.twig',
            [
                'form' => $form->createView(),
                'result' => $result
            ]
        );
    }

    /**
     * @Route("/minitools/skews", name="skews")
     * @param   Request         $request
     * @param   SkewsManager    $skewsManager
     * @return  Response
     * @throws  \Exception
     */
    public function skewsAction(Request $request, SkewsManager $skewsManager)
    {
        $imageResult = "";

        $form = $this->get('form.factory')->create(SkewsType::class);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();

            $aOligoSkew = [];
            $sequence = $formData["seq"];

            // remove useless part of sequence
            if ($formData["from"] or $formData["to"]) {
                if ($skewsManager->strIsInt($formData["to"]) == 1) {
                    $sequence = substr($formData["seq"], 0, $formData["to"]);
                }
                if ($skewsManager->strIsInt($formData["from"]) == 1) {
                    $sequence = substr($formData["seq"], $formData["from"]);
                }
            }

            // when oligo-skew is requested, computing time will be long; let know the user and compute data for oligo-skew
            if ($formData["oskew"] == 1) {
                if ($skewsManager->strIsInt($formData["oligo_len"]) == 1) {
                    // in next line a function will compute an array with distances
                    $aOligoSkew = $skewsManager->oligoSkewArrayCalculation(
                        $sequence,
                        $formData["window"],
                        $formData["oligo_len"],
                        $formData["strands"]
                    );
                }
            }
            // create image with skews
            $imageResult = $skewsManager->createImage(
                $sequence,
                $formData["window"],
                $formData["GC"],
                $formData["AT"],
                $formData["KETO"],
                $formData["GmC"],
                $aOligoSkew,
                $formData["oligo_len"],
                $formData["from"],
                $formData["to"],
                $formData["name"]
            );
        }
        return $this->render(
            'minitools/skews.html.twig',
            [
                'form' => $form->createView(),
                'image' => $imageResult
            ]
        );
    }
}