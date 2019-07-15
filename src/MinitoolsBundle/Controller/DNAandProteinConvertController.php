<?php
/**
 * Minitools controller
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 july 2019
 * Last modified 15 july 2019
 */
namespace MinitoolsBundle\Controller;


use AppBundle\Bioapi\Bioapi;
use MinitoolsBundle\Form\ProteinToDnaType;
use MinitoolsBundle\Service\DnaToProteinManager;
use MinitoolsBundle\Service\ProteinToDnaManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MinitoolsBundle\Form\DnaToProteinType;


class DNAandProteinConvertController extends Controller
{
    /**
     * @Route("/minitools/protein-to-dna", name="protein_to_dna")
     * @param       Request                 $request
     * @param       ProteinToDnaManager     $proteinToDnaManager
     * @return      Response
     * @throws      \Exception
     */
    public function proteinToDnaAction(Request $request, ProteinToDnaManager $proteinToDnaManager)
    {
        $sDna = $sSequence = "";
        $form = $this->get('form.factory')->create(ProteinToDnaType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData       = $form->getData();
            $sSequence = $formData["sequence"];
            $sDna = $proteinToDnaManager->translateProteinToDNA(
                $sSequence,
                $formData["genetic_code"]
            );
        }

        return $this->render(
            '@Minitools/Minitools/proteinToDna.html.twig',
            [
                'form'                  => $form->createView(),
                'sequence'              => $sSequence,
                'dna'                   => $sDna
            ]
        );
    }

    /**
     * @Route("/minitools/dna-to-protein", name="dna_to_protein")
     * @param   Request                 $request
     * @param   DnaToProteinManager     $dnaToProteinManager
     * @param   Bioapi                  $bioapi
     * @return  Response
     * @throws  \Exception
     */
    public function dnaToProteinAction(Request $request, DnaToProteinManager $dnaToProteinManager, Bioapi $bioapi)
    {
        $sResults = $sResultsComplementary  = '';
        $mycode                             = null;
        $aFrames                            = [];
        $bShowAligned                       = false;
        $bDgaps                             = false;

        $aAminoAcidCodes        = $bioapi->getAminosOnlyLetters();
        $aAminoAcidCodesLeft    = array_slice($aAminoAcidCodes, 0, 13);
        $aAminoAcidCodesRight   = array_slice($aAminoAcidCodes, 13);

        $form = $this->get('form.factory')->create(DnaToProteinType::class);

        // Form treatment
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData       = $form->getData();
            $sequence       = $formData["sequence"];
            $bDgaps         = (bool)$formData["dgaps"];
            $bShowAligned   = (bool)$formData["show_aligned"];

            // Custom code
            if (isset($formData['usemycode']) && $formData["usemycode"] == 1) {
                $mycode = $formData["mycode"];
                $aFrames = $dnaToProteinManager->customTreatment($formData["frames"], $sequence, $mycode);
            } else {
                $aFrames = $dnaToProteinManager->definedTreatment(
                    $formData["frames"],
                    $formData["genetic_code"],
                    $sequence
                );
            }

            // FIND ORFs (when requested)
            if((bool)$formData["search_orfs"] ) {
                $aFrames = $dnaToProteinManager->findORF(
                    $aFrames,
                    $formData["protsize"],
                    (bool)$formData["only_coding"],
                    (bool)$formData["trimmed"]
                );
            }

            // Show translations aligned (when requested)
            if((bool)$formData["show_aligned"]) {
                $sResults = $dnaToProteinManager->showTranslationsAligned($sequence, $aFrames);
                $sResultsComplementary = $dnaToProteinManager->showTranslationsAlignedComplementary($aFrames);
            }
        }

        return $this->render(
            '@Minitools/Minitools/dnaToProtein.html.twig',
            [
                'amino_left'            => $aAminoAcidCodesLeft,
                'amino_right'           => $aAminoAcidCodesRight,
                'form'                  => $form->createView(),
                'frames'                => $aFrames,
                'aligned_results'       => $sResults,
                'aligned_results_compl' => $sResultsComplementary,
                'show_aligned'          => $bShowAligned,
                'dgaps'                 => $bDgaps
            ]
        );
    }
}