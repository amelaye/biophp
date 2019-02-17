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
use AppBundle\Entity\Database;
use AppBundle\Service\DatabaseManager;


class DemoController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('@App/Default/index.html.twig');
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
        
        return $this->render('@App/Default/sequenceanalysis.html.twig', 
                array('mirrors' => $aMirrors)
        );
    }

    /**
     * Read a sequence from a database
     * Generates .idx and .dir files
     * @route("/read-sequence-genbank", name="read_sequence_genbank")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function parseaseqdbAction(DatabaseManager $databaseManager)
    {
        $database = new Database("humandb", "GENBANK", "human.seq"); // GENBANK
        $databaseManager->setDatabase($database);
        $databaseManager->buffering(); // Creates the .IDX and .DIR
        $databaseManager->fetch("NM_031438");

        return $this->render('@App/Default/parseseqdb.html.twig');
    }
}
