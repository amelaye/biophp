<?php
/**
 * Demo controller : you can be inspired by this to create you own scripts
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 2 november 2019
 */
namespace AppBundle\Controller;

use AppBundle\Entity\Protein;
use AppBundle\Service\ProteinManager;
use AppBundle\Service\RestrictionEnzymeManager;
use AppBundle\Service\SequenceAlignmentManager;
use AppBundle\Service\SequenceMatchManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Sequence;
use AppBundle\Service\SequenceManager;
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
     * Here is some samples of how to use the functions
     * @route("/sequence-alignment-clustal", name="sequence_alignment_clustal")
     * @param SequenceAlignmentManager $sequenceAlignmentManager
     * @return Response
     * @throws \Exception
     */
    public function clustalseqalignmentAction(SequenceAlignmentManager $sequenceAlignmentManager)
    {
        set_time_limit(0);
        //$sequenceAlignmentManager->setFilename("data/fasta-2.txt");
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        // You wanna sort your array ? :)
        $sequenceAlignmentManager->sortAlpha("ASC");
        dump($sequenceAlignmentManager);
        // You wanna fetch something ?
        $oMySuperSeq = $sequenceAlignmentManager->getSeqSet()->offsetGet(13);
        dump($oMySuperSeq);
        // You wanna know the longest sequence ?
        //$iMyLength = $sequenceAlignmentManager->getMaxiLength();
        // You wanna know the number of gaps ?
        //$iNumberGaps = $sequenceAlignmentManager->getGapCount();
        // Have the same length ?
        //$bIsFlush = $sequenceAlignmentManager->getIsFlush();
        $sCharAtRes = $sequenceAlignmentManager->charAtRes(10, 10);
        dump($sCharAtRes);
        //$sSubstrBwRes = $sequenceAlignmentManager->substrBwRes(10,10);
        //dump($sSubstrBwRes);
        $iColToRes = $sequenceAlignmentManager->colToRes(10, 50);
        $iResToCol = $sequenceAlignmentManager->resToCol(10, 47);
        dump($iResToCol);
        //$sequenceAlignmentManager->subalign(5, 10);
        //dump($sequenceAlignmentManager);
        //$sequenceAlignmentManager->select(1,2,3);
        //dump($sequenceAlignmentManager);

        $aResVar = $sequenceAlignmentManager->resVar();
        dump($aResVar);
        $aConsensus = $sequenceAlignmentManager->consensus();
        dump($aConsensus);

        // Adding a new sequence object
        $sequenceAlignmentManager->addSequence($oMySuperSeq);
        // Dropping a sequence
        $sequenceAlignmentManager->deleteSequence("sp|O09185|P53_CRIGR");

        return $this->render('demo/parseseqalignment.html.twig',
            []
        );
    }

    /**
     * Here is some samples of how to use the functions
     * @route("/play-with-sequencies", name="play_with_sequencies")
     * @param SequenceAlignmentManager $sequenceAlignmentManager
     * @param SequenceManager $sequenceManager
     * @return Response
     * @throws \Exception
     */
    public function playwithsequenciesAction(
        SequenceAlignmentManager $sequenceAlignmentManager,
        SequenceManager $sequenceManager
    ) {
        $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
        $sequenceAlignmentManager->setFormat("FASTA");
        $sequenceAlignmentManager->parseFile();
        $oSequence = $sequenceAlignmentManager->getSeqSet()->offsetGet(0);
        $oSequence->setMolType("DNA");

        $sequenceManager->setSequence($oSequence);

        //$aComplement = $sequenceManager->complement("DNA");
        // dump($aComplement);
        //$sHalfStr = $sequenceManager->halfSequence("GATTAG", 0);
        //dump($sHalfStr);

        //$sBridge = $sequenceManager->getBridge("ATGcacgtcCAT");
        //dump($sBridge);

        //$sExpandNa = $sequenceManager->expandNa("GATTAGSW");
        //dump($sExpandNa);

        //$sMolWt = $sequenceManager->molwt("upperlimit");
        //dump($sMolWt);

        $sCoupe = $sequenceManager->subSeq(2,100);
        //dump($sCoupe);

        //$test = $sequenceManager->patPos("TTT");
        //dump($test);

        //$test = $sequenceManager->patPoso("TTT");
        //dump($test);

        //$symfreq = $sequenceManager->symFreq("A");
        //dump($symfreq);

        //$codon = $sequenceManager->getCodon(3);
        //dump($codon);

        //$translate = $sequenceManager->translate();
        //dump($translate);

        //$charge = $sequenceManager->charge("GAVLIFYWKRH");
        //dump($charge);

        //$charge = $sequenceManager->chemicalGroup("GAVLIFYWKRH");
        //dump($charge);

        //$testPalindrome = $sequenceManager->findPalindrome("", 2, 2);
        $testPalindrome = $sequenceManager->findPalindrome($sCoupe, null,2);
        dump($testPalindrome);

        return $this->render('demo/playwithsequencies.html.twig',
            []
        );
    }

    /**
     * Here is some samples of how to use the functions
     * @route("/play-with-proteins", name="play_with_proteins")
     * @param ProteinManager $proteinManager
     * @return Response
     * @throws \Exception
     */
    public function playwithproteinsAction(ProteinManager $proteinManager)
    {
        $sProtein = "ARNDCEQGHARNDCEQGHILKMFPSTWYVXARNDKMFPSTWYVXARNDKMFPSTWYVXARNDCEQGHARNDCEQGHHARNDCEQGHILKMFPSTW";
        $sProtein .= "YVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWY";
        $sProtein .= "VXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPST";

        $oProtein = new Protein();
        $oProtein->setName("toto");
        $oProtein->setSequence($sProtein);
        $proteinManager->setProtein($oProtein);

        dump($proteinManager->seqlen());
        dump($proteinManager->molwt());

        return $this->render('demo/playwithproteins.html.twig',
            []
        );
    }

    /**
     * Here is some samples of how to use the functions
     * @route("/sequence-match", name="sequence_match")
     * @param   SequenceMatchManager $sequenceMatchManager
     * @return  Response
     * @throws  \Exception
     */
    public function sequencematchManager(SequenceMatchManager $sequenceMatchManager)
    {
        return $this->render('demo/restrictionenzyme.html.twig',
            []
        );
    }

    /**
     * Here is some samples of how to use the functions
     * @route("/restriction-enzyme", name="restriction_enzyme")
     * @param   RestrictionEnzymeManager $restrictionEnzymeManager
     * @return  Response
     * @throws  \Exception
     */
    public function restrictionenzymeAction(
        RestrictionEnzymeManager $restrictionEnzymeManager,
        SequenceManager $sequenceManager,
        SequenceAlignmentManager $sequenceAlignmentManager
    ) {
        $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
        $sequenceAlignmentManager->setFormat("FASTA");
        $sequenceAlignmentManager->parseFile();
        $oSequence = $sequenceAlignmentManager->getSeqSet()->offsetGet(0);
        $oSequence->setMolType("DNA");

        $sequenceManager->setSequence($oSequence);
        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $restrictionEnzymeManager->parseEnzyme('AatI', 'AGGCCT', 0, "inner");

        $cutseq = $restrictionEnzymeManager->cutSeq();
        dump($cutseq);
        $cutseq2 = $restrictionEnzymeManager->cutSeq('O');
        dump($cutseq2);

        $list = $restrictionEnzymeManager->findRestEn("AGGCCT"); // fetchPatternOnly
        dump($list);
        $list2 = $restrictionEnzymeManager->findRestEn("AGGCCT",3); // fetchPatternAndCutpos
        dump($list2);
        $list3 = $restrictionEnzymeManager->findRestEn(null,3); // fetchCutpos
        dump($list3);
        $list4 = $restrictionEnzymeManager->findRestEn(null,null, 6); // fetchLength
        dump($list4);
        $list5 = $restrictionEnzymeManager->findRestEn(null,3, 6); // fetchCutposAndPlen
        dump($list5);

        return $this->render('demo/restrictionenzyme.html.twig',
            []
        );
    }
}
