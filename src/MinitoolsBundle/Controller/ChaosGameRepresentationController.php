<?php
/**
 * Minitools controller
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 july 2019
 * Last modified 23 july 2019
 */
namespace MinitoolsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Service\Misc\OligosManager;
use AppBundle\Traits\OligoTrait;

use MinitoolsBundle\Form\ChaosGameRepresentationType;
use MinitoolsBundle\Service\ChaosGameRepresentationManager;


/**
 * Class ChaosGameRepresentationController
 * @package MinitoolsBundle\Controller
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class ChaosGameRepresentationController extends Controller
{
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
            'minitools/chaosGameRepresentationCGR.html.twig',
            [
                'form'              => $form->createView(),
                'is_computed'       => $isComputed
            ]
        );
    }


    /**
     * @param Request                           $request
     * @param ChaosGameRepresentationManager    $chaosGameReprentationManager
     * @param OligosManager                     $oligosManager
     * @param $form
     * @return Response
     * @throws \Exception
     */
    public function fcgrCompute(Request $request,
                                ChaosGameRepresentationManager $chaosGameReprentationManager, $form, OligosManager $oligosManager)
    {
        $aOligos = $for_map = null;
        $isMap = $isFreq = false;

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $formData = $form->getData();

            $aSeqData = $chaosGameReprentationManager->FCGRCompute($formData["seq"], $formData["len"], $formData["s"]);
            $aNucleotides = $chaosGameReprentationManager->numberNucleos($aSeqData);

            // COMPUTE OLIGONUCLEOTIDE FREQUENCIES
            //      frequencies are saved to an array named $aOligos
            $aOligos = $oligosManager->findOligos($aSeqData["sequence"], $aSeqData["length"]);

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
            'minitools/chaosGameRepresentationFCGR.html.twig',
            [
                'form'              => $form->createView(),
                'oligos'            => $aOligos,
                'areas'             => $for_map,
                'is_map'            => $isMap,
                'show_as_freq'      => $isFreq
            ]
        );
    }
}