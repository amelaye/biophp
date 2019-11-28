<?php
/**
 * Demo controller : you can be inspired by this to create you own scripts
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 2 november 2019
 */
namespace AppBundle\Controller;

use AppBundle\Entity\Sequence;
use AppBundle\Service\SequenceAlignmentManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\IO\DatabaseInterface;
use AppBundle\Service\IO\DatabaseManager;

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
     * Read a sequence from a database
     * Generates .idx and .dir files
     * @route("/read-sequence-genbank", name="read_sequence_genbank")
     * @param DatabaseInterface $databaseManager
     * @return Response
     * @throws \Exception
     */
    public function parseaseqdbAction(DatabaseInterface $databaseManager)
    {
       // $databaseManager->recording("humandb", "GENBANK", "human.seq", "demo.seq");
        $oService = $databaseManager->fetch("NM_031438");
dump($oService);
        $oSequence      = $oService->getSequence();
        $aAccession     = $oService->getAccession();
        $aAuthors       = $oService->getAuthors();
        $oGbSequence    = $oService->getGbSequence();
        $aGbFeatures    = $oService->getGbFeatures();
        $aReferences    = $oService->getReferences();
        $oSrcForm       = $oService->getSrcForm();
        $aKeywords      = $oService->getKeywords();

        return $this->render('demo/parseseqdb.html.twig',
            [
                "sequence"      => $oSequence,
                "accession"     => $aAccession,
                "authors"       => $aAuthors,
                "gbsequence"    => $oGbSequence,
                "gbfeatures"    => $aGbFeatures,
                "references"    => $aReferences,
                "srcform"       => $oSrcForm,
                "keywords"      => $aKeywords
            ]
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

        dump($oSequence);

        exit();
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

        $oMySuperSeq1 = $sequenceAlignmentManager->getSeqSet()->offsetGet(0);
        dump($oMySuperSeq1);
        $oMySuperSeq2 = $sequenceAlignmentManager->getSeqSet()->offsetGet(1);
        dump($oMySuperSeq2);

        return $this->render('demo/parseseqalignment.html.twig',
            []
        );
    }
}
