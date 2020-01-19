<?php
/**
 * Demo controller : you can be inspired by this to create you own scripts
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 18 january 2020
 */
namespace App\Infra\Controller;

use App\Api\ProteinReductionApi;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Domain\Database\Interfaces\DatabaseInterface;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use App\Domain\Sequence\Interfaces\SequenceAlignmentInterface;

/**
 * Class DemoController
 * @package App\Infra\Controller
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
        $databaseManager->recording("humandb", "GENBANK", "human.seq");
        $oService = $databaseManager->fetch("NM_031438");

        dump($oService);

        $oSequence      = $oService->getSequence();
        $aAccession     = $oService->getAccession();
        $aAuthors       = $oService->getAuthors();
        $oGbSequence    = $oService->getGbSequence();
        $aGbFeatures    = $oService->getFeatures();
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
    public function parseaswissprotdbAction(DatabaseManager $databaseManager, ProteinReductionApi $aaa)
    {
        $databaseManager->recording("humandbSwiss", "SWISSPROT", "basicswiss.txt");
        $oSequence = $databaseManager->fetch("1375");

        dump($aaa->getReductions());

        return $this->render('demo/parseswissprotdb.html.twig',
            ["sequence" => $oSequence]
        );
    }

    /**
     * @route("/sequence-alignment-fasta", name="sequence_alignment_fasta")
     * @param SequenceAlignmentInterface $sequenceAlignmentManager
     * @return Response
     */
    public function fastaseqalignmentAction(SequenceAlignmentInterface $sequenceAlignmentManager)
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
