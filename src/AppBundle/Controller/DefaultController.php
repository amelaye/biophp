<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use AppBundle\Entity\Sequence;
use AppBundle\Service\SequenceManager;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
    
    /**
     * @route("/sequence-analysis", name="sequence_analysis")
     * @todo : correct error 
     * The service "AppBundle\Service\SequenceManager" has a dependency on a 
     * non-existent service "AppBundle\Entity\Sequence".
     */
    public function sequenceanalysisAction(SequenceManager $sequenceManager)
    {
        $seq_obj = new Sequence();
        $seq_obj->setSequence("AGGGAATTAAGTAAATGGTAGTGG");
        
        $sequenceManager->setSequence($seq_obj);
        $mirrors = $sequenceManager->find_mirror($seq_obj->getSequence(), 6, 8, "E");
        
        return $this->render('Default/sequenceanalysis.html.twig', 
                array('mirrors' => $mirrors)
        );
    }
}
