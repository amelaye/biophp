<?php
/**
 * Minitools controller
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 july 2019
 * Last modified 23 july 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Controller;


use AppBundle\Service\Misc\GeneticsFunctions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Traits\OligoTrait;

use MinitoolsBundle\Form\ReduceAlphabetType;
use MinitoolsBundle\Form\ProteinPropertiesType;
use MinitoolsBundle\Service\ProteinPropertiesManager;
use MinitoolsBundle\Service\ReduceProteinAlphabetManager;

/**
 * Class ProteinController
 * @package MinitoolsBundle\Controller
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ProteinController extends Controller
{
    use OligoTrait;
    /**
     * @Route("/minitools/protein-properties", name="protein_properties")
     * @param       Request                     $request
     * @param       ProteinPropertiesManager    $proteinPropertiesManager
     * @return      Response
     * @throws      \Exception
     */
    public function proteinPropertiesAction(
        Request $request,
        ProteinPropertiesManager $proteinPropertiesManager
    ){
        $data_source = $three_letter_code = $subsequence    =  "";
        $molweight = $abscoef = $charge = $charge2 = $pH    = 0;
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
            $seq = GeneticsFunctions::removeNonCodingProt($formData["seq"]);
            $subsequence = $proteinPropertiesManager->writeSubsequence($formData["start"], $formData["end"], $seq);
            // calculate nucleotide composition
            $aminoacid_content = $proteinPropertiesManager->aminoacidContent($subsequence);

            // get pk values for charged aminoacids
            $proteinPropertiesManager->setPk($formData["data_source"]);

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
                $charge = $proteinPropertiesManager->proteinIsoelectricPoint($aminoacid_content);
            }
            if ((bool)$formData["charge2"]) {   // calculate charge of protein at requested pH
                $charge2 = $proteinPropertiesManager->proteinCharge($aminoacid_content, $pH);
            }
            if ((bool)$formData["three_letters"]) {  // colored sequence based in plar/non-plar/charged aminoacids
                $three_letter_code = $proteinPropertiesManager->convertInto3lettersCode($subsequence);
            }
            if ((bool)$formData["type1"]) {   // colored sequence based in polar/non-plar/charged aminoacids
                $colored_seq = $proteinPropertiesManager->proteinAminoacidNature1($subsequence, $colors);
            }
            if ((bool)$formData["type2"]) {
                $colored_seq2 = $proteinPropertiesManager->proteinAminoacidNature2($subsequence, $colors);
            }
        }

        return $this->render(
            'minitools/proteinProperties.html.twig',
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
     * @Route("/minitools/reduce-protein-alphabet", name="reduce_protein_alphabet")
     * @param       Request                         $request
     * @param       ReduceProteinAlphabetManager    $reduceProteinAlphabetManager
     * @return      Response
     * @throws      \Exception
     */
    public function reduceProteinAlphabetAction(
        Request $request,
        ReduceProteinAlphabetManager $reduceProteinAlphabetManager
    ) {
        $form = $this->get('form.factory')->create(ReduceAlphabetType::class);
        $reducedCode = $reducedSeq = "";

        $sMode = $sCustomAlphabet = $sSequence = $sType = $sAaperline = "";
        $bShowReduced = false;

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData       = $form->getData();
            $sMode              = $formData["mode"];
            $sCustomAlphabet    = $formData["custom_alphabet"];
            $sSequence          = $formData["seq"];
            $bShowReduced       = $formData["show_reduced"];
            $sType              = $formData["type"];
            $sAaperline         = $formData["aaperline"];

            // REDUCE ALPHABET
            if ($sMode == "pre") {
                // for predefined reduced alphabets
                if ($sSequence != ""  && $sType != "" && $sAaperline != "") {
                    $reducedSeq = $reduceProteinAlphabetManager->reduceAlphabet($sSequence, $sType);
                    $reducedCode = $reduceProteinAlphabetManager->createReduceCode($sType);
                }
            } else {
                // for personalized reduced alphabets
                if ($sSequence != "" && $sAaperline != "") {
                    $reducedSeq =  $reduceProteinAlphabetManager->reduceAlphabetCustom($sSequence, $sCustomAlphabet);
                }
            }
        }

        return $this->render(
            'minitools/reduceProteinAlphabet.html.twig',
            [
                'form'                  => $form->createView(),
                'reduced_code'          => $reducedCode,
                'reduced_seq'           => $reducedSeq,
                'mode'                  => $sMode,
                'custom_alphabet'       => $sCustomAlphabet,
                'sequence'              => $sSequence,
                'show_reduced'          => $bShowReduced,
                'type'                  => $sType,
                'aa_perline'            => $sAaperline
            ]
        );
    }
}