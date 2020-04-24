<?php
namespace Tests\Domain\Sequence\Entity;

use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SequenceTest extends WebTestCase
{
    public function testNewSequence()
    {
        $oExpectedSequence = new Sequence();
        $oExpectedSequence->setPrimAcc("NM_031438");
        $oExpectedSequence->setSeqLength(3488);
        $oExpectedSequence->setMolType("mRNA");
        $oExpectedSequence->setDate("29-DEC-2018");
        $oExpectedSequence->setSource("Homo sapiens (human)");
        $oExpectedSequence->setEntryName("test entry");
        $sSequence = "aagactgcat ccggctccag gaaaagcgag tgggatatcc caatctttgg actgcatcct ggttgcctct actgtggtca ";
        $sSequence.= "cctttgggaa gaaatgtctt ctgtaaaaag aagtctgaag caagaaatag ttactcagtt tcactgttca gctgctgaag ";
        $sSequence.= "gagatattgc caagttaaca ggaatactca gtcattctcc atctcttctc aatgaaactt ctgaaaatgg ctggactgct ";
        $sSequence.= "ttaatgtatg cggcaaggaa tgggcaccca gagatagtcc aatttctgct tgagaaaggg tgtgacagat caattgtcaa ";
        $sSequence.= "taaatcaagg cagactgcac tggacattgc tgtattttgg ggttataagc atatagctaa tttactagct actgctaaag ";
        $sSequence.= "gtgggaagaa gccttggttc ctaacgaatg aagtggaaga atgtgaaaat tattttagca aaacactact ggaccggaaa ";
        $sSequence.= "agtgaaaaga gaaataattc tgactggctg ctagctaaag aaagccatcc agccacagtt tttattcttt tctcagattt ";
        $sSequence.= "aaatcccttg gttactctag gtggcaataa agaaagtttc caacagccag aagttaggct ttgtcagctg aactacacag ";
        $sSequence.= "atataaagga ttatttggcc cagcctgaga agatcacctt gatttttctt ggagtagaac ttgaaataaa agacaaacta ";
        $sSequence.= "cttaattatg ctggtgaagt cccgagagag gaggaagatg gattggttgc ctggtttgct ctaggtatag atcctattgc ";
        $sSequence.= "tgctgaagaa ttcaagcaaa gacatgaaaa ttgttacttt cttcatcctc ctatgccagc ccttctgcaa ttgaaagaaa ";
        $sSequence.= "aagaagctgg ggttgtagct caagcaagat ctgttcttgc ctggcacagt cgatacaagt tttgcccaac ctgtggaaat ";
        $sSequence.= "gcaactaaaa ttgaagaagg tggctataag agattatgtt taaaagaaga ctgtcctagt ctcaatggcg tccataatac ";
        $sSequence.= "ctcataccca agagttgatc cagtagtaat catgcaagtt attcatccag atgggaccaa atgcctttta ggcaggcaga ";
        $sSequence.= "aaagatttcc cccaggcatg tttacttgcc ttgctggatt tattgagcct ggagagacaa tagaagatgc tgttaggaga ";
        $sSequence.= "gaagtagaag aggaaagtgg agtcaaagtt ggccatgttc agtatgttgc ttgtcaacca tggccaatgc cttcctcctt ";
        $sSequence.= "aatgattggt tgcttagctc tagcagtgtc tacagaaatt aaagttgaca agaatgaaat agaggatgcc cgctggttca ";
        $sSequence.= "ctagagaaca ggtcctggat gttctgacca aagggaagca gcaggcattc tttgtgccac caagccgagc tattgcacat ";
        $sSequence.= "caattaatca aacactggat tagaataaat cctaatctct aaatctaaga actaagcttt gagtattatt taataatttc ";
        $sSequence.= "taataacact cattcctcaa gtgatattag agattattca gtactcttga gagtgtcaca acacaaaata cgatgttggg ";
        $sSequence.= "ttttcgaaat attttcaaag tgttctgtct taatcacaaa ttcatatttt tacacatttt tacaatattg cctcagatta ";
        $sSequence.= "tgttaaattt gggtcagtct tctctgaact ttttctctct tggtttcttt tcttccttca cagttttatc tcacaaaacc ";
        $sSequence.= "atttttctaa taagagacat catgttggaa agatgttcta gaaatgtgca taaatttcag tgcctcttgt aagcattaaa ";
        $sSequence.= "ctgatgatga agaaagttcc tgatttgaga aatgaatcaa agtaatttta atgaattttt agcttgtatt agcttgagtt ";
        $sSequence.= "agctggcatt gattttttag tccttttgtt acctttaagt tgtcaatata tggtttttgt tcatctcccc attgtagtcc ";
        $sSequence.= "cacttgctct ttcctggggg ttccattgtt ctagcagtgg aggtgttata gtgtcgccac tcgtctaatt tgaccagtgt ";
        $sSequence.= "taagaatttt ctaatttaat aatttaatag tgatctcaat accacaccct catggaagga gaaaagcata ctattatatc ";
        $sSequence.= "tgggacctct cttttagacc taaaattaat taacatatct acttatatgt tacttatacc taaagctgtt attaagacaa ";
        $sSequence.= "accaagattc tctgcttttg cactgaaatt aaacttgaaa ggaattctcc tcaaaggtcg gatattaaat aagtcccagg ";
        $sSequence.= "cagatttaca tatttaattt aaaacattgg ctttatttca ttttgtgatg agtgatgtat ctgtgttaac aaaaaattgt ";
        $sSequence.= "ataatcatta ccaatactat ttattatgct caaatatatc ttggctttga ccttatttca acacattcta agaagccttg ";
        $sSequence.= "acaaagtaag tatattttag agctgaatca gtaagattct agagaaagca aaacatagta gttcacaatt ttgcaacata ";
        $sSequence.= "gaaagtcaca ttttgaaagg ctattttgaa attgatttaa tagctattat agtttatgaa tatcaaaatt tgtataattt ";
        $sSequence.= "gcatctttac taatgtatgc tagagctaca agagacctta aggataatat atgaaattag ctttccttat tttatagata ";
        $sSequence.= "aggaaaaaga aattgtgaaa ggtgaattta cctaattagt gaaagttaca taactaatta caacagtctg tactatataa ";
        $sSequence.= "tgcagaggac gattctccct gtaaaaggaa ctagaagcta ttactaaaaa tatatataga caaaattaaa agaaggaatg ";
        $sSequence.= "ataagaataa atttaattta ccaaatattg ttaattaaaa ttttagatac ttaacattta tttaacttaa ataaaagata ";
        $sSequence.= "actgtcagat aaaactttat tttactaatg agcagtgatt ttcttaggaa ttgatgaagg cttattggta tcaagaattt ";
        $sSequence.= "aaaccaaatt aaaactgaca gaggacattt agatacataa taaaattcga gctacataag tatatggaaa ataatgtacc ";
        $sSequence.= "ttgattatta tgaaatagag catcttgaaa ttcagtttta ctctaaatgt acttttaata cttgcagatt ctaagattac ";
        $sSequence.= "attgtaaaat tccaggtttt cataatgtta aaataggaaa gtagaatata aagtatcaac aagtgtagtt atacattttg ";
        $sSequence.= "ttttggatat ttaatcctta cttgggaaaa aatcagcatc taggtaaatt attattttaa taacaactct taaattgcca ";
        $sSequence.= "acctctgaga ggtgaaaagc tatgtaaata gaaggaatgg ccagttcaaa agaatagtag atgtgatagt gccgtgaatg ";
        $sSequence.= "tattctactg gaaatgaatg taataataca ttaaattttt aaaatcta";
        $oExpectedSequence->setSequence($sSequence);
        $oExpectedSequence->setDescription("Homo sapiens nudix hydrolase 12 (NUDT12), transcript variant 1, mRNA.");
        $organism = ['Homo sapiens','Eukaryota','Metazoa','Chordata', 'Craniata', 'Vertebrata', 'Euteleostomi', 'Mammalia',
            'Eutheria','Euarchontoglires','Primates','Haplorrhini','Catarrhini','Hominidae','Homo.'];

        $oExpectedSequence->setOrganism($organism);
        $oExpectedSequence->setStart(10);
        $oExpectedSequence->setEnd(20);
        $oExpectedSequence->setFragment(1);

        $this->assertEquals("NM_031438", $oExpectedSequence->getPrimAcc());
        $this->assertEquals("test entry", $oExpectedSequence->getEntryName());
        $this->assertEquals(3488, $oExpectedSequence->getSeqLength());
        $this->assertEquals(10, $oExpectedSequence->getStart());
        $this->assertEquals(20, $oExpectedSequence->getEnd());
        $this->assertEquals("mRNA", $oExpectedSequence->getMolType());
        $this->assertEquals("29-DEC-2018", $oExpectedSequence->getDate());
        $this->assertEquals("Homo sapiens (human)", $oExpectedSequence->getSource());
        $this->assertEquals($sSequence, $oExpectedSequence->getSequence());
        $this->assertEquals("Homo sapiens nudix hydrolase 12 (NUDT12), transcript variant 1, mRNA.", $oExpectedSequence->getDescription());
        $this->assertEquals($organism, $oExpectedSequence->getOrganism());
        $this->assertEquals(1, $oExpectedSequence->getFragment());
    }
}