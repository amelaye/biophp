<?php
/**
 * Demo controller
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Sequence;
use AppBundle\Service\SequenceManager;

class DemoController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // Prévoir menu et sommaire
    }
    
    /**
     * @route("/sequence-analysis", name="sequence_analysis")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sequenceanalysisAction(SequenceManager $sequenceManager)
    {
        $oSequence = new Sequence();
        $oSequence->setSequence("AGGGAATTAAGTAAATGGTAGTGG");
        $sequenceManager->setSequence($oSequence);
        
        $aMirrors = $sequenceManager->find_mirror($oSequence->getSequence(), 6, 8, "E");
        dump($aMirrors);
        
        return $this->render('@App/Default/sequenceanalysis.html.twig', 
                array('mirrors' => $aMirrors)
        );
    }
}
