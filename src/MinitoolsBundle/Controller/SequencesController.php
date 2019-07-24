<?php
/**
 * Minitools controller
 * Freely inspired by BioPHP's project biophp.org
 * Created 22 july 2019
 * Last modified 22 july 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use MinitoolsBundle\Service\FindPalindromeManager;
use MinitoolsBundle\Service\DistanceAmongSequencesManager;
use MinitoolsBundle\Service\RandomSequencesManager;
use MinitoolsBundle\Service\SequenceAlignmentManager;
use MinitoolsBundle\Form\SequenceAlignmentType;
use MinitoolsBundle\Form\FindPalindromesType;
use MinitoolsBundle\Form\DistanceAmongSequencesType;
use MinitoolsBundle\Form\RandomSequencesType;

/**
 * Class ProteinController
 * @package MinitoolsBundle\Controller
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class SequencesController extends Controller
{
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
            $min = $formData["min"];
            $max = $formData["max"];
            $aPalindromes = $oFindPalindromeManager->findPalindromicSeqs($formData["seq"], $min, $max);
        }

        return $this->render(
            'minitools/findPalindromes.html.twig',
            [
                'form'          => $form->createView(),
                'min'           => $min,
                'max'           => $max,
                'palindromes'   => $aPalindromes
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
                $oligo_array = $oDistanceAmongSequencesManager->computeOligonucleotidsFrequenciesEuclidean($seqs,
                    $length);
                $data = $oDistanceAmongSequencesManager->computeDistancesAmongFrequenciesEuclidean($seqs,
                    $oligo_array,$length);
            } else {
                $oligo_array = $oDistanceAmongSequencesManager->computeOligonucleotidsFrequencies($seqs);

                $data = $oDistanceAmongSequencesManager->computeDistancesAmongFrequencies($seqs, $oligo_array);
            }

            $dendogramFile = $this->getParameter('nucleotids_graphs')['dendogram_file'];
            $oDistanceAmongSequencesManager->upgmaClustering($data, $formData["method"], $length, $dendogramFile);
        }

        return $this->render(
            'minitools/distanceAmongSequences.html.twig',
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
     * @Route("/minitools/random-seqs", name="random_seqs")
     * @param       Request                     $request
     * @param       RandomSequencesManager      $randomSequencesManager
     * @return      Response
     * @throws      \Exception
     */
    public function randomSeqsAction(Request $request, RandomSequencesManager $randomSequencesManager)
    {
        $result             = "";
        $aAminoAcids        = [];

        $form = $this->get('form.factory')->create(RandomSequencesType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData       = $form->getData();
            switch($formData["procedure"]) {
                case "fromseq":
                    $result = $randomSequencesManager->createFromSeq($formData["length1"], $formData["seq"]);
                    break;
                case "fromACGT":
                    $length2 = $formData["length2"];
                    $alp = ["A", "C", "G", "T"];
                    foreach($alp as $letter){
                        $aAminoAcids[$letter] = $formData["dna".$letter];
                    }
                    $result = $randomSequencesManager->createFromACGT($aAminoAcids, $length2);
                    break;
                case "fromAA":
                    $length3 = $formData["length3"];
                    $alp = ["A", "C", "D", "E", "F", "G", "H", "I", "K", "L", "M", "N", "P", "Q", "R", "S", "T",
                        "V", "W", "Y"];
                    foreach($alp as $letter){
                        $aAminoAcids[$letter] = $formData[strtolower($letter)];
                    }
                    $result = $randomSequencesManager->createFromAA($aAminoAcids, $length3);
            }
        }

        return $this->render(
            'minitools/randomSeqs.html.twig',
            [
                'form'         => $form->createView(),
                'results'      => $result
            ]
        );
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
        $form = $this->get('form.factory')->create(SequenceAlignmentType::class);
        $sCompare = $sAlignSeqA = $sAlignSeqB = "";
        $id1 = $id2 = "";

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData       = $form->getData();
            $id1 = $formData["id1"];
            $id2 = $formData["id2"];

            // CHECK WHETHER THEY ARE DNA OR PROTEIN, AND ALIGN SEQUENCES
            if ((substr_count($formData["sequence"],"A")
                    + substr_count($formData["sequence"],"C")
                    + substr_count($formData["sequence"],"G")
                    + substr_count($formData["sequence"],"T")
                ) > (strlen($formData["sequence"]) / 2)) {
                // if A+C+G+T is at least half of the sequence, it is a DNA
                $aAlignment = $sequenceAlignmentManager->alignDNA($formData["sequence"], $formData["sequence2"]);
            } else {
                // else is protein
                $aAlignment = $sequenceAlignmentManager->alignProteins($formData["sequence"], $formData["sequence2"]);
            }

            // EXTRACT DATA FROM ALIGNMENT
            $sAlignSeqA = $aAlignment["seqa"];
            $sAlignSeqB = $aAlignment["seqb"];

            // COMPARE ALIGNMENTS
            $sCompare = $sequenceAlignmentManager->compareAlignment($sAlignSeqA, $sAlignSeqB);
        }

        return $this->render(
            'minitools/seqAlignment.html.twig',
            [
                'form'          => $form->createView(),
                'compare'       => $sCompare,
                'align_seqa'    => $sAlignSeqA,
                'align_seqb'    => $sAlignSeqB,
                'sequence1'     => $id1,
                'sequence2'     => $id2,
            ]
        );
    }
}