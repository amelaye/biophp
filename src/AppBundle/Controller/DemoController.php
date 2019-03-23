<?php
/**
 * Demo controller : you can be inspired by this to create you own scripts
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 21 february 2019
 */
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Sequence;
use AppBundle\Service\SequenceManager;
use AppBundle\Entity\Database;
use AppBundle\Service\DatabaseManager;

/**
 * Class DemoController
 * @package AppBundle\Controller
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class DemoController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('@App/demo/index.html.twig');
    }

    /**
     * @route("/sequence-analysis", name="sequence_analysis")
     * @param SequenceManager $sequenceManager
     * @return Response
     */
    public function sequenceanalysisAction(SequenceManager $sequenceManager)
    {
        $oSequence = new Sequence();
        $oSequence->setSequence("AGGGAATTAAGTAAATGGTAGTGG");
        $sequenceManager->setSequence($oSequence);
        
        $aMirrors = $sequenceManager->find_mirror($oSequence->getSequence(), 6, 8, "E");
        
        return $this->render('@App/demo/sequenceanalysis.html.twig',
            array('mirrors' => $aMirrors)
        );
    }

    /**
     * Read a sequence from a database
     * Generates .idx and .dir files
     * @route("/read-sequence-genbank", name="read_sequence_genbank")
     * @param DatabaseManager $databaseManager
     * @return Response
     * @throws \Exception
     */
    public function parseaseqdbAction(DatabaseManager $databaseManager)
    {
        $database = new Database("humandb", "GENBANK", "human.seq"); // GENBANK
        $databaseManager->setDatabase($database);
        $databaseManager->buffering(); // Creates the .IDX and .DIR
        $oSequence = $databaseManager->fetch("NM_031438");

        return $this->render('@App/demo/parseseqdb.html.twig',
            ["sequence" => $oSequence]
        );
    }

    /**
     * Read a sequence from a database
     * Generates .idx and .dir files
     * @route("/read-sequence-swissprot", name="read_sequence_swissprot")
     * @param DatabaseManager $databaseManager
     * @return Response
     * @throws \Exception
     */
    public function parseaswissprotdbAction(DatabaseManager $databaseManager)
    {
        $database = new Database("humandbBis", "SWISSPROT", "Q5K4E3.txt"); // SWISSPROT
        $databaseManager->setDatabase($database);
        $databaseManager->buffering(); // Creates the .IDX and .DIR
        //$databaseManager->fetch("NM_031438");

        return $this->render('@App/demo/parseswissprotdb.html.twig');
    }
}
