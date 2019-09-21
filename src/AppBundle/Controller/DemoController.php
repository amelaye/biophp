<?php
/**
 * Demo controller : you can be inspired by this to create you own scripts
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 21 february 2019
 */
namespace AppBundle\Controller;

use AppBundle\Service\SequenceAlignmentManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Sequence;
use AppBundle\Service\SequenceManager;
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
        return $this->render('demo/index.html.twig');
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
        
        return $this->render('demo/sequenceanalysis.html.twig',
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
        $databaseManager->recording("humandb", "GENBANK", "human.seq", "demo.seq");
        $oSequence = $databaseManager->fetch("NM_031438");

        return $this->render('demo/parseseqdb.html.twig',
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
        $databaseManager->recording("humandbSwiss", "SWISSPROT", "basicswiss.txt");
        $oSequence = $databaseManager->fetch("1375");
        return $this->render('demo/parseswissprotdb.html.twig',
            ["sequence" => $oSequence]
        );
    }

    /**
     * @route("/sequence-alignment-fasta", name="sequence_alignment_fasta")
     * @param SequenceAlignmentManager $sequenceAlignmentManager
     * @return Response
     */
    public function fastaseqalignmentAction(SequenceAlignmentManager $sequenceAlignmentManager)
    {
        $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
        //$sequenceAlignmentManager->setFilename("data/human-fasta.txt");
        $sequenceAlignmentManager->setFormat("FASTA");
        $sequenceAlignmentManager->parseFile();


        return $this->render('demo/parseseqalignment.html.twig',
            []
        );
    }

    /**
     * @route("/sequence-alignment-clustal", name="sequence_alignment_clustal")
     * @param SequenceAlignmentManager $sequenceAlignmentManager
     * @return Response
     * @throws \Exception
     */
    public function clustalseqalignmentAction(SequenceAlignmentManager $sequenceAlignmentManager)
    {
        //$sequenceAlignmentManager->setFilename("data/fasta-2.txt");
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        // You wanna sort your array ? :)
        $sequenceAlignmentManager->sortAlpha();
        // You wanna fetch something ?
        $oMySuperSeq = $sequenceAlignmentManager->fetch(13);
        // You wanna know the longest sequence ?
        $iMyLength = $sequenceAlignmentManager->getMaxiLength();
        // You wanna know the number of gaps ?
        $iNumberGaps = $sequenceAlignmentManager->getGapCount();
        // Have the same length ?
        $bIsFlush = $sequenceAlignmentManager->getIsFlush();

        $sCharAtRes = $sequenceAlignmentManager->charAtRes(10, 10);


        $sSubstrBwRes = $sequenceAlignmentManager->substrBwRes(10,10);
        dump($sSubstrBwRes);

        return $this->render('demo/parseseqalignment.html.twig',
            []
        );
    }
}
