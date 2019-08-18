<?php
namespace Tests\MinitoolsBundle\Service;

use MinitoolsBundle\Service\RestrictionDigestManager;
use PHPUnit\Framework\TestCase;

class RestrictionDigestManagerTest extends TestCase
{
    protected $aType2;

    protected $type2s;

    protected $type2b;

    public function setUp()
    {
        $vendorLinks = [
          "C" => [
            "name" => "Minotech Biotechnology",
            "url" => "http://www.minotech.gr",
          ],
          "E" => [
            "name" => "Minotech Stratagene",
            "url" => "http://www.stratagene.com",
          ],
          "F" => [
            "name" => "Fermentas AB",
            "url" => "http://www.fermentas.com",
          ],
          "H" => [
            "name" => "American Allied Biochemical, Inc.",
            "url" => "http://www.aablabs.com",
          ],
          "I" => [
            "name" => "SibEnzyme Ltd.",
            "url" => "http://www.sibenzyme.com",
          ],
          "J" => [
            "name" => "Nippon Gene Co., Ltd.",
            "url" => "http://www.nippongene.jp",
          ],
          "K" => [
            "name" => "Takara Shuzo Co. Ltd.",
            "url" => "http://www.takarashuzo.co.jp/english/index.htm",
          ],
          "M" => [
            "name" => "Roche Applied Science",
            "url" => "http://www.roche.com",
          ],
          "N" => [
            "name" => "New England Biolabs",
            "url" => "http://www.neb.com",
          ],
          "O" => [
            "name" => "Toyobo Biochemicals",
            "url" => "http://www.toyobo.co.jp/e/",
          ],
          "P" => [
            "name" => "Megabase Research Products",
            "url" => "http://www.cvienzymes.com",
          ],
          "Q" => [
            "name" => "CHIMERx",
            "url" => "http://www.CHIMERx.com",
          ],
          "R" => [
            "name" => "Promega Corporation",
            "url" => "http://www.promega.com",
          ],
          "S" => [
            "name" => "Sigma Chemical Corporation",
            "url" => "http://www.sigmaaldrich.com",
          ],
          "U" => [
            "name" => "Bangalore Genei",
            "url" => "http://www.bangaloregenei.com",
          ],
          "V" => [
            "name" => "MRC-Holland",
            "url" => "http://www.mrc-holland.com",
          ],
          "X" => [
            "name" => "EURx Ltd.",
            "url" => "http://www.eurx.com.pl/index.php?op=catalog&cat=8",
          ],
        ];

        $this->aType2 = [
          "AasI" => [
            0 => "AasI,DrdI,DseDI",
            1 => "GACNN_NN'NNGTC",
            2 => "(GAC......GTC)",
            3 => 12,
            4 => 7,
            5 => -2,
            6 => 6,
          ],
          "AatI" => [
            0 => "AatI,Eco147I,PceI,SseBI,StuI",
            1 => "AGG'CCT",
            2 => "(AGGCCT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AatII" => [
            0 => "AatII",
            1 => "G_ACGT'C",
            2 => "(GACGTC)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "AbsI" => [
            0 => "AbsI",
            1 => "CC'TCGA_GG",
            2 => "(CCTCGAGG)",
            3 => 8,
            4 => 6,
            5 => -4,
            6 => 8,
          ],
          "Acc16I" => [
            0 => "Acc16I,AviII,FspI,NsbI",
            1 => "TGC'GCA",
            2 => "(TGCGCA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Acc65I" => [
            0 => "Acc65I,Asp718I",
            1 => "G'GTAC_C",
            2 => "(GGTACC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AccB1I" => [
            0 => "AccB1I,BanI,BshNI,BspT107I",
            1 => "G'GYRC_C",
            2 => "(GGCACC|GGCGCC|GGTACC|GGTGCC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AccB7I" => [
            0 => "AccB7I,BasI,PflMI,Van91I",
            1 => "CCAN_NNN'NTGG",
            2 => "(CCA.....TGG)",
            3 => 11,
            4 => 7,
            5 => -3,
            6 => 6,
          ],
          "AccBSI" => [
            0 => "AccBSI,BsrBI,MbiI",
            1 => "CCG'CTC",
            2 => "(CCGCTC|GAGCGG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AccI" => [
            0 => "AccI,FblI,XmiI",
            1 => "GT'MK_AC",
            2 => "(GTCTAC|GTCGAC|GTATAC|GTAGAC)",
            3 => 6,
            4 => 2,
            5 => 2,
            6 => 6,
          ],
          "AccII" => [
            0 => "AccII,Bsh1236I,BspFNI,BstFNI,BstUI,MvnI",
            1 => "CG'CG",
            2 => "(CGCG)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "AccIII" => [
            0 => "AccIII,Aor13HI,BlfI,BseAI,Bsp13I,BspEI,Kpn2I,MroI",
            1 => "T'CCGG_A",
            2 => "(TCCGGA)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AciI" => [
            0 => "AciI,BspACI,SsiI",
            1 => "C'CG_C or G'CG_G",
            2 => "(CCGC|GCGG)",
            3 => 4,
            4 => 1,
            5 => 2,
            6 => 4,
          ],
          "AclI" => [
            0 => "AclI,Psp1406I",
            1 => "AA'CG_TT",
            2 => "(AACGTT)",
            3 => 6,
            4 => 2,
            5 => 2,
            6 => 6,
          ],
          "AcoI" => [
            0 => "AcoI",
            1 => "Y'CCGG_R",
            2 => "(CCCGGA|CCCGGG|TCCGGA|TCCGGG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AcsI" => [
            0 => "AcsI,ApoI,XapI",
            1 => "R'AATT_Y",
            2 => "(AAATTC|AAATTT|GAATTC|GAATTT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AcvI" => [
            0 => "AcvI,BbrPI,Eco72I,PmaCI,PmlI,PspCI",
            1 => "CAC'GTG",
            2 => "(CACGTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AcyI" => [
            0 => "AcyI,BsaHI,BssNI,BstACI,Hin1I,Hsp92I",
            1 => "GR'CG_YC",
            2 => "(GACGCC|GACGTC|GGCGCC|GGCGTC)",
            3 => 6,
            4 => 2,
            5 => 2,
            6 => 6,
          ],
          "AdeI" => [
            0 => "AdeI,DraIII",
            1 => "CAC_NNN'GTG",
            2 => "(CAC...GTG)",
            3 => 9,
            4 => 6,
            5 => -3,
            6 => 6,
          ],
          "AfaI" => [
            0 => "AfaI,RsaI",
            1 => "GT'AC",
            2 => "(GTAC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "AfeI" => [
            0 => "AfeI,Aor51HI,Eco47III",
            1 => "AGC'GCT",
            2 => "(AGCGCT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AfiI" => [
            0 => "AfiI,Bsc4I,BseLI,BsiYI,BslI",
            1 => "CCNN_NNN'NNGG",
            2 => "(CC.......GG)",
            3 => 11,
            4 => 7,
            5 => -3,
            6 => 4,
          ],
          "AflII" => [
            0 => "AflII,BfrI,BspTI,Bst98I,BstAFI,MspCI,Vha464I",
            1 => "C'TTAA_G",
            2 => "(CTTAAG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AflIII" => [
            0 => "AflIII",
            1 => "A'CRYG_T",
            2 => "(ACACGT|ACATGT|ACGCGT|ACGTGT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AgeI" => [
            0 => "AgeI,AsiGI,BshTI,CspAI,PinAI",
            1 => "A'CCGG_T",
            2 => "(ACCGGT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AgSI" => [
            0 => "AgSI",
            1 => "TT_S'AA",
            2 => "(TTGAA|TTCAA)",
            3 => 5,
            4 => 3,
            5 => -1,
            6 => 5,
          ],
          "AhdI" => [
            0 => "AhdI,AspEI,BmeRI,DriI,Eam1105I,EclHKI",
            1 => "GACNN_N'NNGTC",
            2 => "(GAC.....GTC)",
            3 => 11,
            4 => 6,
            5 => -1,
            6 => 6,
          ],
          "AhlI" => [
            0 => "AhlI,BcuI,SpeI",
            1 => "A'CTAG_T",
            2 => "(ACTAGT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AjiI" => [
            0 => "AjiI,BmgBI,BtrI",
            1 => "CAC'GTC",
            2 => "(CACGTC|GACGTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AjnI" => [
            0 => "AjnI,EcoRII,Psp6I,PspGI",
            1 => "'CCWGG_",
            2 => "(CCAGG|CCTGG)",
            3 => 5,
            4 => 0,
            5 => 5,
            6 => 5,
          ],
          "AleI" => [
            0 => "AleI,OliI",
            1 => "CACNN'NNGTG",
            2 => "(CAC....GTG)",
            3 => 10,
            4 => 5,
            5 => 0,
            6 => 6,
          ],
          "AluI" => [
            0 => "AluI,AluBI",
            1 => "AG'CT",
            2 => "(AGCT)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "Alw21I" => [
            0 => "Alw21I,BsiHKAI,Bbv12I",
            1 => "G_WGCW'C",
            2 => "(GAGCAC|GAGCTC|GTGCAC|GTGCTC)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "Alw44I" => [
            0 => "Alw44I,ApaLI,VneI",
            1 => "G'TGCA_C",
            2 => "(GTGCAC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AlwNI" => [
            0 => "AlwNI,CaiI,PstNI",
            1 => "CAG_NNN'CTG",
            2 => "(CAG...CTG)",
            3 => 9,
            4 => 6,
            5 => -3,
            6 => 6,
          ],
          "Ama87I" => [
            0 => "Ama87I,AvaI,BmeT110I,BsiHKCI,BsoBI,Eco88I",
            1 => "C'YCGR_G",
            2 => "(CCCGAG|CCCGGG|CTCGAG|CTCGGG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "ApaI" => [
            0 => "ApaI",
            1 => "G_GGCC'C",
            2 => "(GGGCCC)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "ApeKI" => [
            0 => "ApeKI,TseI",
            1 => "G'CWG_C",
            2 => "(GCAGC|GCTGC)",
            3 => 5,
            4 => 1,
            5 => 3,
            6 => 5,
          ],
          "AscI" => [
            0 => "AscI,PalAI,SgsI",
            1 => "GG'CGCG_CC",
            2 => "(GGCGCGCC)",
            3 => 8,
            4 => 2,
            5 => 4,
            6 => 8,
          ],
          "AseI" => [
            0 => "AseI,PshBI,VspI",
            1 => "AT'TA_AT",
            2 => "(ATTAAT)",
            3 => 6,
            4 => 2,
            5 => 2,
            6 => 6,
          ],
          "AsiSI" => [
            0 => "AsiSI,RgaI,SfaAI,SgfI",
            1 => "GCG_AT'CGC",
            2 => "(GCGATCGC)",
            3 => 8,
            4 => 5,
            5 => -2,
            6 => 8,
          ],
          "Asp700I" => [
            0 => "Asp700I,MroXI,PdmI,XmnI",
            1 => "GAANN'NNTTC",
            2 => "(GAA....TTC)",
            3 => 10,
            4 => 5,
            5 => 0,
            6 => 6,
          ],
          "AspA2I" => [
            0 => "AspA2I,AvrII,BlnI,XmaJI",
            1 => "C'CTAG_G",
            2 => "(CCTAGG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AspI" => [
            0 => "AspI,PflFI,PsyI,Tth111I",
            1 => "GACN'N_NGTC",
            2 => "(GAC...GTC)",
            3 => 9,
            4 => 4,
            5 => 1,
            6 => 6,
          ],
          "AspLEI" => [
            0 => "AspLEI,BstHHI,CfoI,HhaI",
            1 => "G_CG'C",
            2 => "(GCGC)",
            3 => 4,
            4 => 3,
            5 => -2,
            6 => 4,
          ],
          "AspS9I" => [
            0 => "AspS9I,BmgT120I,Cfr13I,PspPI,Sau96I",
            1 => "G'GNC_C",
            2 => "(GG.CC)",
            3 => 5,
            4 => 1,
            5 => 3,
            6 => 4,
          ],
          "AssI" => [
            0 => "AssI,BmcAI,ScaI,ZrmI",
            1 => "AGT'ACT",
            2 => "(AGTACT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AsuC2I" => [
            0 => "AsuC2I,BcnI,BpuMI,NciI",
            1 => "CC'S_GG",
            2 => "(CCGGG|CCCGG)",
            3 => 5,
            4 => 2,
            5 => 1,
            6 => 5,
          ],
          "AsuII" => [
            0 => "AsuII,Bpu14I,Bsp119I,BspT104I,BstBI,Csp45I,NspV,SfuI",
            1 => "TT'CG_AA",
            2 => "(TTCGAA)",
            3 => 6,
            4 => 2,
            5 => 2,
            6 => 6,
          ],
          "AsuNHI" => [
            0 => "AsuNHI,BspOI,NheI",
            1 => "G'CTAG_C",
            2 => "(GCTAGC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "AvaII" => [
            0 => "AvaII,Bme18I,Eco47I,SinI,VpaK11BI",
            1 => "G'GWC_C",
            2 => "(GGACC|GGTCC)",
            3 => 5,
            4 => 1,
            5 => 3,
            6 => 5,
          ],
          "AxyI" => [
            0 => "AxyI,Bse21I,Bsu36I,Eco81I",
            1 => "CC'TNA_GG",
            2 => "(CCT.AGG)",
            3 => 7,
            4 => 2,
            5 => 3,
            6 => 6,
          ],
          "BaeGI" => [
            0 => "BaeGI,BseSI,BstSLI",
            1 => "G_KGCM'C",
            2 => "(GGGCAC|GGGCCC|GTGCAC|GTGCCC)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "BalI" => [
            0 => "BalI,MlsI,MluNI,MscI,Msp20I",
            1 => "TGG'CCA",
            2 => "(TGGCCA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BamHI" => [
            0 => "BamHI",
            1 => "G'GATC_C",
            2 => "(GGATCC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BanII" => [
            0 => "BanII,Eco24I,EcoT38I,FriOI",
            1 => "G_RGCY'C",
            2 => "(GAGCCC|GAGCTC|GGGCCC|GGGCTC)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "BanIII" => [
            0 => "BanIII,Bsa29I,BseCI,BshVI,BspDI,BspXI,Bsu15I,BsuTUI,ClaI",
            1 => "AT'CG_AT",
            2 => "(ATCGAT)",
            3 => 6,
            4 => 2,
            5 => 2,
            6 => 6,
          ],
          "BauI" => [
            0 => "BauI",
            1 => "C'ACGA_G",
            2 => "(CACGAG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BbeI" => [
            0 => "BbeI,PluTI",
            1 => "G_GCGC'C",
            2 => "(GGCGCC)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "BbuI" => [
            0 => "BbuI,PaeI,SphI",
            1 => "G_CATG'C",
            2 => "(GCATGC)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "BbvCI" => [
            0 => "BbvCI",
            1 => "CC'TCA_GC or  GC'TGA_GG",
            2 => "(CCTCAGC|GCTGAGG)",
            3 => 7,
            4 => 2,
            5 => 3,
            6 => 7,
          ],
          "BciT130I" => [
            0 => "BciT130I,BseBI,BstNI,BstOI,Bst2UI,MvaI",
            1 => "CC'W_GG",
            2 => "(CCAGG|CCTGG)",
            3 => 5,
            4 => 2,
            5 => 1,
            6 => 5,
          ],
          "BclI" => [
            0 => "BclI,FbaI,Ksp22I",
            1 => "T'GATC_A",
            2 => "(TGATCA)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BfaI" => [
            0 => "BfaI,FspBI,MaeI,XspI",
            1 => "C'TA_G",
            2 => "(CTAG)",
            3 => 4,
            4 => 1,
            5 => 2,
            6 => 4,
          ],
          "BfmI" => [
            0 => "BfmI,BpcI,BstSFI,SfcI",
            1 => "C'TRYA_G",
            2 => "(CTACAG|CTATAG|CTGCAG|CTGTAG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BfoI" => [
            0 => "BfoI,BstH2I,HaeII",
            1 => "R_GCGC'Y",
            2 => "(AGCGCC|AGCGCT|GGCGCC|GGCGCT)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "BfuCI" => [
            0 => "BfuCI,Bsp143I,BssMI,BstMBI,DpnII,Kzo9I,MboI,NdeII,Sau3AI",
            1 => "'GATC_",
            2 => "(GATC)",
            3 => 4,
            4 => 0,
            5 => 4,
            6 => 4,
          ],
          "BglI" => [
            0 => "BglI",
            1 => "GCCN_NNN'NGGC",
            2 => "(GCC.....GGC)",
            3 => 11,
            4 => 7,
            5 => -3,
            6 => 6,
          ],
          "BglII" => [
            0 => "BglII",
            1 => "A'GATC_T",
            2 => "(AGATCT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BisI" => [
            0 => "BisI,BlsI,Fnu4HI,Fsp4HI,GluI,ItaI,SatI",
            1 => "GC'N_GC",
            2 => "(GC.GC)",
            3 => 5,
            4 => 2,
            5 => 1,
            6 => 4,
          ],
          "BlpI" => [
            0 => "BlpI,Bpu1102I,Bsp1720I,CelII",
            1 => "GC'TNA_GC",
            2 => "(GCT.AGC)",
            3 => 7,
            4 => 2,
            5 => 3,
            6 => 6,
          ],
          "Bme1390I" => [
            0 => "Bme1390I,MspR9I,ScrFI",
            1 => "CC'N_GG",
            2 => "(CC.GG)",
            3 => 5,
            4 => 2,
            5 => 1,
            6 => 4,
          ],
          "BmiI" => [
            0 => "BmiI,BspLI,NlaIV,PspN4I",
            1 => "GGN'NCC",
            2 => "(GG..CC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 4,
          ],
          "BmrFI" => [
            0 => "BmrFI,BssKI,BstSCI,StyD4I",
            1 => "'CCNGG_",
            2 => "(CC.GG)",
            3 => 5,
            4 => 0,
            5 => 5,
            6 => 4,
          ],
          "BmtI" => [
            0 => "BmtI",
            1 => "G_CTAG'C",
            2 => "(GCTAGC)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "BoxI" => [
            0 => "BoxI,PshAI,BstPAI",
            1 => "GACNN'NNGTC",
            2 => "(GAC....GTC)",
            3 => 10,
            4 => 5,
            5 => 0,
            6 => 6,
          ],
          "BptI" => [
            0 => "BptI",
            1 => "CC'W_GG",
            2 => "(CCAGG|CCTGG)",
            3 => 5,
            4 => 2,
            5 => 1,
            6 => 5,
          ],
          "Bpu10I" => [
            0 => "Bpu10I",
            1 => "CC'TNA_GC",
            2 => "(CCT.AGC|GCT.AGG)",
            3 => 7,
            4 => 2,
            5 => 3,
            6 => 6,
          ],
          "BpvUI" => [
            0 => "BpvUI,MvrI,PvuI,Ple19I",
            1 => "CG_AT'CG",
            2 => "(CGATCG)",
            3 => 6,
            4 => 4,
            5 => -2,
            6 => 6,
          ],
          "BsaAI" => [
            0 => "BsaAI,BstBAI,Ppu21I",
            1 => "YAC'GTR",
            2 => "(CACGTA|CACGTG|TACGTA|TACGTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BsaBI" => [
            0 => "BsaBI,Bse8I,BseJI,MamI",
            1 => "GATNN'NNATC",
            2 => "(GAT....ATC)",
            3 => 10,
            4 => 5,
            5 => 0,
            6 => 6,
          ],
          "BsaJI" => [
            0 => "BsaJI,BseDI,BssECI",
            1 => "C'CNNG_G",
            2 => "(CC..GG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 4,
          ],
          "BsaWI" => [
            0 => "BsaWI",
            1 => "W'CCGG_W",
            2 => "(ACCGGA|ACCGGT|TCCGGA|TCCGGT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "Bse118I" =>  [
            0 => "Bse118I,BsrFI,BssAI,Cfr10I",
            1 => "R'CCGG_Y",
            2 => "(ACCGGC|ACCGGT|GCCGGC|GCCGGT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BsePI" =>  [
            0 => "BsePI,BssHII,PauI,PteI",
            1 => "G'CGCG_C",
            2 => "(GCGCGC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BseX3I" => [
            0 => "BseX3I,BstZI,EagI,EclXI,Eco52I",
            1 => "C'GGCC_G",
            2 => "(CGGCCG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BseYI" => [
            0 => "BseYI",
            1 => "C'CCAG_C",
            2 => "(CCCAGC|GCTGGG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "Bsh1285I" => [
            0 => "Bsh1285I,BsiEI,BstMCI",
            1 => "CG_RY'CG",
            2 => "(CGACCG|CGATCG|CGGCCG|CGGTCG)",
            3 => 6,
            4 => 4,
            5 => -2,
            6 => 6,
          ],
          "BshFI" => [
            0 => "BshFI,BsnI,BspANI,BsuRI,HaeIII,PhoI",
            1 => "GG'CC",
            2 => "(GGCC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "BsiSI" => [
            0 => "BsiSI,HapII,HpaII,MspI",
            1 => "C'CG_G",
            2 => "(CCGG)",
            3 => 4,
            4 => 1,
            5 => 2,
            6 => 4,
          ],
          "BsiWI" => [
            0 => "BsiWI,Pfl23II,PspLI",
            1 => "C'GTAC_G",
            2 => "(CGTACG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "Bsp120I" => [
            0 => "Bsp120I,PspOMI",
            1 => "G'GGCC_C",
            2 => "(GGGCCC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "Bsp1286I" => [
            0 => "Bsp1286I,MhlI,SduI",
            1 => "G_DGCH'C",
            2 => "(GAGCAC|GAGCTC|GAGCCC|GTGCAC|GTGCTC|GTGCCC|GGGCAC|GGGCTC|GGGCCC)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "Bsp1407I" => [
            0 => "Bsp1407I,BsrGI,BstAUI,SspBI",
            1 => "T'GTAC_A",
            2 => "(TGTACA)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "Bsp19I" => [
            0 => "Bsp19I,NcoI",
            1 => "C'CATG_G",
            2 => "(CCATGG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "Bsp68I" => [
            0 => "Bsp68I,BtuMI,NruI,RruI",
            1 => "TCG'CGA",
            2 => "(TCGCGA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BspHI" => [
            0 => "BspHI,CciI,PagI,RcaI",
            1 => "T'CATG_A",
            2 => "(TCATGA)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BspLU11I" => [
            0 => "BspLU11I,PciI,PscI",
            1 => "A'CATG_T",
            2 => "(ACATGT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BspMAI" => [
            0 => "BspMAI,PstI",
            1 => "C_TGCA'G",
            2 => "(CTGCAG)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "BssNAI" => [
            0 => "BssNAI,Bst1107I,BstZ17I",
            1 => "GTA'TAC",
            2 => "(GTATAC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BssSI" => [
            0 => "BssSI,Bst2BI",
            1 => "C'ACGA_G or C'TCGT_G",
            2 => "(CACGAG|CTCGTG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BssT1I" => [
            0 => "BssT1I,StyI,Eco130I,EcoT14I,ErhI",
            1 => "C'CWWG_G",
            2 => "(CCAAGG|CCATGG|CCTAGG|CCTTGG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "Bst4CI" => [
            0 => "Bst4CI,HpyCH4III,TaaI",
            1 => "AC_N'GT",
            2 => "(AC.GT)",
            3 => 5,
            4 => 3,
            5 => -1,
            6 => 4,
          ],
          "BstAPI" => [
            0 => "BstAPI",
            1 => "GCAN_NNN'NTGC",
            2 => "(GCA.....TGC)",
            3 => 11,
            4 => 7,
            5 => -3,
            6 => 6,
          ],
          "BstC8I" => [
            0 => "BstC8I,Cac8I",
            1 => "GCN'NGC",
            2 => "(GC..GC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 4,
          ],
          "BstDEI" => [
            0 => "BstDEI,DdeI,HpyF3I",
            1 => "C'TNA_G",
            2 => "(CT.AG)",
            3 => 5,
            4 => 1,
            5 => 3,
            6 => 4,
          ],
          "BstDSI" => [
            0 => "BstDSI,BtgI",
            1 => "C'CRYG_G",
            2 => "(CCACGG|CCATGG|CCGCGG|CCGTGG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BstEII" => [
            0 => "BstEII,BstPI,Eco91I,EcoO65I,PspEI",
            1 => "G'GTNAC_C",
            2 => "(GGT.ACC)",
            3 => 7,
            4 => 1,
            5 => 5,
            6 => 6,
          ],
          "BstENI" => [
            0 => "BstENI,EcoNI,XagI",
            1 => "CCTNN'N_NNAGG",
            2 => "(CCT.....AGG)",
            3 => 11,
            4 => 5,
            5 => 1,
            6 => 6,
          ],
          "BstKTI" => [
            0 => "BstKTI",
            1 => "G_AT'C",
            2 => "(GATC)",
            3 => 4,
            4 => 3,
            5 => 2,
            6 => 4,
          ],
          "BstMWI" => [
            0 => "BstMWI,MwoI",
            1 => "GCNN_NNN'NNGC",
            2 => "(GC.......GC)",
            3 => 11,
            4 => 7,
            5 => -3,
            6 => 4,
          ],
          "BstNSI" => [
            0 => "BstNSI,NspI,XceI",
            1 => "R_CATG'Y",
            2 => "(ACATGC|ACATGT|GCATGC|GCATGT)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "BstSNI" => [
            0 => "BstSNI,Eco105I,SnaBI",
            1 => "TAC'GTA",
            2 => "(TACGTA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BstX2I" => [
            0 => "BstX2I,BstYI,MflI,PsuI,XhoII",
            1 => "R'GATC_Y",
            2 => "(AGATCC|AGATCT|GGATCC|GGATCT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "BstXI" => [
            0 => "BstXI",
            1 => "CCAN_NNNN'NTGG",
            2 => "(CCA......TGG)",
            3 => 12,
            4 => 8,
            5 => -4,
            6 => 6,
          ],
          "CciNI" => [
            0 => "CciNI,NotI",
            1 => "GC'GGCC_GC",
            2 => "(GCGGCCGC)",
            3 => 8,
            4 => 2,
            5 => 4,
            6 => 8,
          ],
          "Cfr42I" => [
            0 => "Cfr42I,KspI,SacII,Sfr303I,SgrBI,SstII",
            1 => "CC_GC'GG",
            2 => "(CCGCGG)",
            3 => 6,
            4 => 4,
            5 => -2,
            6 => 6,
          ],
          "Cfr9I" => [
            0 => "Cfr9I,TspMI,XmaI,XmaCI",
            1 => "C'CCGG_G",
            2 => "(CCCGGG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "CfrI" => [
            0 => "CfrI,EaeI",
            1 => "Y'GGCC_R",
            2 => "(CGGCCA|CGGCCG|TGGCCA|TGGCCG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "CpoI" => [
            0 => "CpoI,CspI,RsrII,Rsr2I",
            1 => "CG'GWC_CG",
            2 => "(CGGACCG|CGGTCCG)",
            3 => 7,
            4 => 2,
            5 => 3,
            6 => 7,
          ],
          "CsiI" => [
            0 => "CsiI,MabI,SexAI",
            1 => "A'CCWGG_T",
            2 => "(ACCAGGT|ACCTGGT)",
            3 => 7,
            4 => 1,
            5 => 5,
            6 => 7,
          ],
          "Csp6I" => [
            0 => "Csp6I,CviQI,RsaNI",
            1 => "G'TA_C",
            2 => "(GTAC)",
            3 => 4,
            4 => 1,
            5 => 2,
            6 => 4,
          ],
          "CviAII" => [
            0 => "CviAII,FaeI,Hin1II,Hsp92II,NlaIII",
            1 => "_CATG'",
            2 => "(CATG)",
            3 => 4,
            4 => 4,
            5 => -4,
            6 => 4,
          ],
          "CviJI" => [
            0 => "CviJI,CviKI-1",
            1 => "RG'CY",
            2 => "(AGCC|AGCT|GGCC|GGCT)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "DinI" => [
            0 => "DinI,Mly113I,NarI,SspDI",
            1 => "GG'CG_CC",
            2 => "(GGCGCC)",
            3 => 6,
            4 => 2,
            5 => 2,
            6 => 6,
          ],
          "DpnI" => [
            0 => "DpnI,MalI",
            1 => "GA'TC",
            2 => "(GATC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "DraI" => [
            0 => "DraI",
            1 => "TTT'AAA",
            2 => "(TTTAAA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Ecl136II" => [
            0 => "Ecl136II,Eco53kI,EcoICRI",
            1 => "GAG'CTC",
            2 => "(GAGCTC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Eco32I" => [
            0 => "Eco32I,EcoRV",
            1 => "GAT'ATC",
            2 => "(GATATC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "EcoO109I" => [
            0 => "EcoO109I,DraII",
            1 => "RG'GNC_CY",
            2 => "(AGG.CCC|AGG.CCT|GGG.CCC|GGG.CCT)",
            3 => 7,
            4 => 2,
            5 => 3,
            6 => 6,
          ],
          "EcoRI" => [
            0 => "EcoRI",
            1 => "G'AATT_C",
            2 => "(GAATTC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "EcoT22I" => [
            0 => "EcoT22I,Mph1103I,NsiI,Zsp2I",
            1 => "A_TGCA'T",
            2 => "(ATGCAT)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "EgeI" => [
            0 => "EgeI,EheI,SfoI",
            1 => "GGC'GCC",
            2 => "(GGCGCC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "FaiI" => [
            0 => "FaiI",
            1 => "YA'TR",
            2 => "(CATA|CATG|TATA|TATG)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "FatI" => [
            0 => "FatI",
            1 => "'CATG_",
            2 => "(CATG)",
            3 => 4,
            4 => 0,
            5 => 4,
            6 => 4,
          ],
          "FauNDI" => [
            0 => "FauNDI,NdeI",
            1 => "CA'TA_TG",
            2 => "(CATATG)",
            3 => 6,
            4 => 2,
            5 => 2,
            6 => 6,
          ],
          "FseI" => [
            0 => "FseI,RigI",
            1 => "GG_CCGG'CC",
            2 => "(GGCCGGCC)",
            3 => 8,
            4 => 6,
            5 => -4,
            6 => 8,
          ],
          "FspAI" => [
            0 => "FspAI",
            1 => "RTGC'GCAY",
            2 => "(ATGCGCAC|ATGCGCAT|GTGCGCAC|GTGCGCAT)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "GlaI" => [
            0 => "GlaI",
            1 => "GC'GC",
            2 => "(GCGC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "GsaI" => [
            0 => "GsaI",
            1 => "C_CCAG'C",
            2 => "(CCCAGC|GCTGGG)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "Hin6I" => [
            0 => "Hin6I,HinP1I,HspAI",
            1 => "G'CG_C",
            2 => "(GCGC)",
            3 => 4,
            4 => 1,
            5 => 2,
            6 => 4,
          ],
          "HincII" => [
            0 => "HincII,HindII",
            1 => "GTY'RAC",
            2 => "(GTCAAC|GTCGAC|GTTAAC|GTTGAC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "HindIII" => [
            0 => "HindIII",
            1 => "A'AGCT_T",
            2 => "(AAGCTT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "HinfI" => [
            0 => "HinfI",
            1 => "G'ANT_C",
            2 => "(GA.TC)",
            3 => 5,
            4 => 1,
            5 => 3,
            6 => 4,
          ],
          "HpaI" => [
            0 => "HpaI,KspAI",
            1 => "GTT'AAC",
            2 => "(GTTAAC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Hpy166II" => [
            0 => "Hpy166II,Hpy8I",
            1 => "GTN'NAC",
            2 => "(GT..AC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 4,
          ],
          "Hpy188I" => [
            0 => "Hpy188I",
            1 => "TC_N'GA",
            2 => "(TC.GA)",
            3 => 5,
            4 => 3,
            5 => -1,
            6 => 4,
          ],
          "Hpy188III" => [
            0 => "Hpy188III",
            1 => "TC'NN_GA",
            2 => "(TC..GA)",
            3 => 6,
            4 => 2,
            5 => 2,
            6 => 4,
          ],
          "Hpy99I" => [
            0 => "Hpy99I",
            1 => "_CGWCG'",
            2 => "(CGACG|CGTCG)",
            3 => 5,
            4 => 5,
            5 => -5,
            6 => 5,
          ],
          "HpyCH4IV" => [
            0 => "HpyCH4IV,HpySE526I,MaeII",
            1 => "A'CG_T",
            2 => "(ACGT)",
            3 => 4,
            4 => 1,
            5 => 2,
            6 => 4,
          ],
          "HpyCH4V" => [
            0 => "HpyCH4V",
            1 => "TG'CA",
            2 => "(TGCA)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "HpyF10VI" => [
            0 => "HpyF10VI",
            1 => "GCNN_NNN'NNGC",
            2 => "(GC.......GC)",
            3 => 11,
            4 => 7,
            5 => -3,
            6 => 4,
          ],
          "KasI" => [
            0 => "KasI",
            1 => "G'GCGC_C",
            2 => "(GGCGCC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "KflI" => [
            0 => "KflI",
            1 => "GG'GWC_CC",
            2 => "(GGGACCC|GGGTCCC)",
            3 => 7,
            4 => 2,
            5 => 3,
            6 => 7,
          ],
          "KpnI" => [
            0 => "KpnI",
            1 => "G_GTAC'C",
            2 => "(GGTACC)",
            3 => 6,
            4 => 5,
            5 => -4,
            6 => 6,
          ],
          "KroI" => [
            0 => "KroI,MroNI,NgoMIV",
            1 => "G'CCGG_C",
            2 => "(GCCGGC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "MaeIII" => [
            0 => "MaeIII",
            1 => "'GTNAC_",
            2 => "(GT.AC)",
            3 => 5,
            4 => 0,
            5 => 5,
            6 => 4,
          ],
          "MauBI" => [
            0 => "MauBI",
            1 => "CG'CGCG_CG",
            2 => "(CGCGCGCG)",
            3 => 8,
            4 => 2,
            5 => 4,
            6 => 8,
          ],
          "MfeI" => [
            0 => "MfeI,MunI",
            1 => "C'AATT_G",
            2 => "(CAATTG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "MluCI" => [
            0 => "MluCI,Sse9I,TasI,Tsp509I,TspEI",
            1 => "'AATT_",
            2 => "(AATT)",
            3 => 4,
            4 => 0,
            5 => 4,
            6 => 4,
          ],
          "MluI" => [
            0 => "MluI",
            1 => "A'CGCG_T",
            2 => "(ACGCGT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "MreI" => [
            0 => "MreI",
            1 => "CG'CCGG_CG",
            2 => "(CGCCGGCG)",
            3 => 8,
            4 => 2,
            5 => 4,
            6 => 8,
          ],
          "MseI" => [
            0 => "MseI,SaqAI,Tru1I,Tru9I",
            1 => "T'TA_A",
            2 => "(TTAA)",
            3 => 4,
            4 => 1,
            5 => 2,
            6 => 4,
          ],
          "MslI" => [
            0 => "MslI,RseI,SmiMI",
            1 => "CAYNN'NNRTG",
            2 => "(CAC....ATG|CAC....GTG|CAT....ATG|CAT....GTG)",
            3 => 10,
            4 => 5,
            5 => 0,
            6 => 6,
          ],
          "MspA1I" => [
            0 => "MspA1I",
            1 => "CMG'CKG",
            2 => "(CAGCGG|CAGCTG|CCGCGG|CCGCTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "MssI" => [
            0 => "MssI,PmeI",
            1 => "GTTT'AAAC",
            2 => "(GTTTAAAC)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "NaeI" => [
            0 => "NaeI,PdiI",
            1 => "GCC'GGC",
            2 => "(GCCGGC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "NmuCI" => [
            0 => "NmuCI,TseFI,Tsp45I",
            1 => "'GTSAC_",
            2 => "(GTCAC|GTGAC)",
            3 => 5,
            4 => 0,
            5 => 5,
            6 => 5,
          ],
          "PacI" => [
            0 => "PacI",
            1 => "TTA_AT'TAA",
            2 => "(TTAATTAA)",
            3 => 8,
            4 => 5,
            5 => -2,
            6 => 8,
          ],
          "PaeR7I" => [
            0 => "PaeR7I,Sfr274I,SlaI,StrI,TliI,XhoI",
            1 => "C'TCGA_G",
            2 => "(CTCGAG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "PasI" => [
            0 => "PasI",
            1 => "CC'CWG_GG",
            2 => "(CCCAGGG|CCCTGGG)",
            3 => 7,
            4 => 2,
            5 => 3,
            6 => 7,
          ],
          "PcsI" => [
            0 => "PcsI",
            1 => "WCGNNN_N'NNNCGW",
            2 => "(ACG.......CGA|ACG.......CGT|TCG.......CGA|TCG.......CGT)",
            3 => 13,
            4 => 7,
            5 => -1,
            6 => 6,
          ],
          "PfeI" => [
            0 => "PfeI,TfiI",
            1 => "G'AWT_C",
            2 => "(GAATC|GATTC)",
            3 => 5,
            4 => 1,
            5 => 3,
            6 => 5,
          ],
          "PfoI" => [
            0 => "PfoI",
            1 => "T'CCNGG_A",
            2 => "(TCC.GGA)",
            3 => 7,
            4 => 1,
            5 => 5,
            6 => 6,
          ],
          "PpuMI" => [
            0 => "PpuMI,Psp5II,PspPPI",
            1 => "RG'GWC_CY",
            2 => "(AGGACCC|AGGACCT|AGGTCCC|AGGTCCT|GGGACCC|GGGACCT|GGGTCCC|GGGTCCT)",
            3 => 7,
            4 => 2,
            5 => 3,
            6 => 7,
          ],
          "PsiI" => [
            0 => "AanI,PsiI",
            1 => "TTA'TAA",
            2 => "(TTATAA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Psp124BI" => [
            0 => "Psp124BI,SacI,SstI",
            1 => "G_AGCT'C",
            2 => "(GAGCTC)",
            3 => 6,
            4 => 5,
            5 => -44,
            6 => 6,
          ],
          "PspXI" => [
            0 => "PspXI",
            1 => "VC'TCGA_GB",
            2 => "(ACTCGAGC|ACTCGAGG|ACTCGAGT|CCTCGAGC|CCTCGAGG|CCTCGAGT|GCTCGAGC|GCTCGAGG|GCTCGAGT)",
            3 => 8,
            4 => 2,
            5 => 4,
            6 => 8,
          ],
          "PvuII" => [
            0 => "PvuII",
            1 => "CAG'CTG",
            2 => "(CAGCTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "SalI" => [
            0 => "SalI",
            1 => "G'TCGA_C",
            2 => "(GTCGAC)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "SbfI" => [
            0 => "SbfI,SdaI,Sse8387I",
            1 => "CC_TGCA'GG",
            2 => "(CCTGCAGG)",
            3 => 8,
            4 => 6,
            5 => -4,
            6 => 8,
          ],
          "SetI" => [
            0 => "SetI",
            1 => "_ASST'",
            2 => "(AGGT|AGCT|ACGT|ACCT)",
            3 => 4,
            4 => 4,
            5 => -4,
            6 => 4,
          ],
          "SfiI" => [
            0 => "SfiI",
            1 => "GGCCN_NNN'NGGCC",
            2 => "(GGCC.....GGCC)",
            3 => 13,
            4 => 8,
            5 => -3,
            6 => 8,
          ],
          "SgrAI" => [
            0 => "SgrAI",
            1 => "CR'CCGG_YG",
            2 => "(CACCGGCG|CACCGGTG|CGCCGGCG|CGCCGGTG)",
            3 => 8,
            4 => 2,
            5 => 4,
            6 => 8,
          ],
          "SgrDI" => [
            0 => "SgrDI",
            1 => "CG'TCGA_CG",
            2 => "(CGTCGACG)",
            3 => 8,
            4 => 2,
            5 => 4,
            6 => 8,
          ],
          "SmaI" => [
            0 => "SmaI",
            1 => "CCC'GGG",
            2 => "(CCCGGG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "SmiI" => [
            0 => "SmiI,SwaI",
            1 => "ATTT'AAAT",
            2 => "(ATTTAAAT)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "SmlI" => [
            0 => "SmlI,SmoI",
            1 => "C'TYRA_G",
            2 => "(CTCAAG|CTCGAG|CTTAAG|CTTGAG)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "SrfI" => [
            0 => "SrfI",
            1 => "GCCC'GGGC",
            2 => "(GCCCGGGC)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "SspI" => [
            0 => "SspI",
            1 => "AAT'ATT",
            2 => "(AATATT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "TaiI" => [
            0 => "TaiI",
            1 => "_ACGT'",
            2 => "(ACGT)",
            3 => 4,
            4 => 4,
            5 => -4,
            6 => 4,
          ],
          "TaqI" => [
            0 => "TaqI",
            1 => "T'CG_A",
            2 => "(TCGA)",
            3 => 4,
            4 => 1,
            5 => 2,
            6 => 4,
          ],
          "TatI" => [
            0 => "TatI",
            1 => "W'GTAC_W",
            2 => "(AGTACA|AGTACT|TGTACA|TGTACT)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "TauI" => [
            0 => "TauI",
            1 => "G_CSG'C",
            2 => "(GCCGC|GCGGC)",
            3 => 5,
            4 => 4,
            5 => -3,
            6 => 5,
          ],
          "TscAI" => [
            0 => "TscAI,TspRI",
            1 => "_NNCASTGNN'",
            2 => "(..CACTG..|..CAGTG..)",
            3 => 9,
            4 => 9,
            5 => -9,
            6 => 5,
          ],
          "XbaI" => [
            0 => "XbaI",
            1 => "T'CTAG_A",
            2 => "(TCTAGA)",
            3 => 6,
            4 => 1,
            5 => 4,
            6 => 6,
          ],
          "XcmI" => [
            0 => "XcmI",
            1 => "CCANNNN_N'NNNNTGG",
            2 => "(CCA.........TGG)",
            3 => 15,
            4 => 8,
            5 => -1,
            6 => 6,
          ],
          "ZraI" => [
            0 => "ZraI",
            1 => "GAC'GTC",
            2 => "(GACGTC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ]
        ];

        $this->type2s = [
            "AarI" => [
                0 => "AarI",
                1 => "CACCTGCNNNN'NNNN_",
                2 => "(CACCTGC........)",
                3 => 15,
                4 => 11,
                5 => 4,
                6 => 7,
            ],
            "AarI@" => [
                0 => "",
                1 => "",
                2 => "(........GCAGGTG)",
                3 => 15,
                4 => 0,
                5 => 4,
                6 => 7,
            ],
            "AbaSI" => [
                0 => "AbaSI",
                1 => "CNNNNNNNNN_NN'",
                2 => "(C...........)",
                3 => 11,
                4 => 11,
                5 => -2,
                6 => 1,
            ],
            "AbaSI@" => [
                0 => "",
                1 => "",
                2 => "(...........G)",
                3 => 11,
                4 => 2,
                5 => -2,
                6 => 1,
            ],
            "Acc36I" => [
                0 => "Acc36I,BfuAI,BspMI,BveI",
                1 => "ACCTGCNNNN'NNNN_",
                2 => "(ACCTGC........)",
                3 => 14,
                4 => 10,
                5 => 4,
                6 => 6,
            ],
            "Acc36I@" => [
                0 => "",
                1 => "",
                2 => "(........GCAGGT)",
                3 => 14,
                4 => 0,
                5 => 4,
                6 => 6,
            ],
            "AclWI" => [
                0 => "AclWI,AlwI,BspPI",
                1 => "GGATCNNNN'N_",
                2 => "(GGATC.....)",
                3 => 10,
                4 => 9,
                5 => 1,
                6 => 5,
            ],
            "AclWI@" => [
                0 => "",
                1 => "",
                2 => "(.....GATCC)",
                3 => 10,
                4 => 0,
                5 => 1,
                6 => 5,
            ],
            "AcuI" => [
                0 => "AcuI,Eco57I",
                1 => "CTGAAGNNNNNNNNNNNNNN_NN'",
                2 => "(CTGAAG................)",
                3 => 22,
                4 => 22,
                5 => -2,
                6 => 6,
            ],
            "AcuI@" => [
                0 => "",
                1 => "",
                2 => "(................CTTCAG)",
                3 => 22,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "Alw26I" => [
                0 => "Alw26I,BcoDI,BsmAI,BstMAI",
                1 => "GTCTCN'NNNN_",
                2 => "(GTCTC.....)",
                3 => 10,
                4 => 6,
                5 => 4,
                6 => 5,
            ],
            "Alw26I@" => [
                0 => "",
                1 => "",
                2 => "(.....GAGAC)",
                3 => 10,
                4 => 0,
                5 => 4,
                6 => 5,
            ],
            "ArsI" => [
                0 => "ArsI",
                1 => "GACNNNNNNTTYGNNNNNN_NNNNN'",
                2 => "(GAC......TTCG...........|GAC......TTTG...........)",
                3 => 24,
                4 => 24,
                5 => -5,
                6 => 7,
            ],
            "ArsI@" => [
                0 => "",
                1 => "",
                2 => "(...........CGAA......GTC|...........CAAA......GTC)",
                3 => 24,
                4 => 5,
                5 => -5,
                6 => 7,
            ],
            "AsuHPI" => [
                0 => "AsuHPI,HphI",
                1 => "GGTGANNNNNNN_N'",
                2 => "(GGTGA........)",
                3 => 13,
                4 => 13,
                5 => -1,
                6 => 5,
            ],
            "AsuHPI@" => [
                0 => "",
                1 => "",
                2 => "(........TCACC)",
                3 => 13,
                4 => 1,
                5 => -1,
                6 => 5,
            ],
            "BbsI" => [
                0 => "BbsI,BpiI,BpuAI,BstV2I",
                1 => "GAAGACNN'NNNN_",
                2 => "(GAAGAC......)",
                3 => 12,
                4 => 8,
                5 => 4,
                6 => 6,
            ],
            "BbsI@" => [
                0 => "",
                1 => "",
                2 => "(......GTCTTC)",
                3 => 12,
                4 => 0,
                5 => 4,
                6 => 6,
            ],
            "BbvI" => [
                0 => "BbvI,BseXI,BstV1I,Lsp1109I",
                1 => "GCAGCNNNNNNNN'NNNN_",
                2 => "(GCAGC............)",
                3 => 17,
                4 => 13,
                5 => 4,
                6 => 5,
            ],
            "BbvI@" => [
                0 => "",
                1 => "",
                2 => "(............GCTGC)",
                3 => 17,
                4 => 0,
                5 => 4,
                6 => 5,
            ],
            "BccI" => [
                0 => "BccI",
                1 => "CCATCNNNN'N_",
                2 => "(CCATC.....)",
                3 => 10,
                4 => 9,
                5 => 1,
                6 => 5,
            ],
            "BccI@" => [
                0 => "",
                1 => "",
                2 => "(.....GATGG)",
                3 => 10,
                4 => 0,
                5 => 1,
                6 => 5,
            ],
            "BceAI" => [
                0 => "BceAI",
                1 => "ACGGCNNNNNNNNNNNN'NN_",
                2 => "(ACGGC..............)",
                3 => 19,
                4 => 17,
                5 => 2,
                6 => 5,
            ],
            "BceAI@" => [
                0 => "",
                1 => "",
                2 => "(..............GCCGT)",
                3 => 19,
                4 => 0,
                5 => 2,
                6 => 5,
            ],
            "BciVI" => [
                0 => "BciVI,BfuI,BsuI",
                1 => "GTATCCNNNNN_N'",
                2 => "(GTATCC......)",
                3 => 12,
                4 => 12,
                5 => -1,
                6 => 6,
            ],
            "BciVI@" => [
                0 => "",
                1 => "",
                2 => "(......GGATAC)",
                3 => 12,
                4 => 1,
                5 => -1,
                6 => 6,
            ],
            "BfiI" => [
                0 => "BfiI,BmrI,BmuI",
                1 => "ACTGGGNNNN_N'",
                2 => "(ACTGGG.....)",
                3 => 11,
                4 => 11,
                5 => -1,
                6 => 6,
            ],
            "BfiI@" => [
                0 => "",
                1 => "",
                2 => "(.....CCCAGT)",
                3 => 11,
                4 => 1,
                5 => -1,
                6 => 6,
            ],
            "BmsI" => [
                0 => "BmsI,LweI,SfaNI",
                1 => "GCATCNNNNN'NNNN_",
                2 => "(GCATC.........)",
                3 => 14,
                4 => 10,
                5 => 4,
                6 => 5,
            ],
            "BmsI@" => [
                0 => "",
                1 => "",
                2 => "(.........GATGC)",
                3 => 14,
                4 => 0,
                5 => 4,
                6 => 5,
            ],
            "BpmI" => [
                0 => "BpmI,GsuI",
                1 => "CTGGAGNNNNNNNNNNNNNN_NN'",
                2 => "(CTGGAG................)",
                3 => 22,
                4 => 22,
                5 => -2,
                6 => 6,
            ],
            "BpmI@" => [
                0 => "",
                1 => "",
                2 => "(................CTCCAG)",
                3 => 22,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "BpuEI" => [
                0 => "BpuEI",
                1 => "CTTGAGNNNNNNNNNNNNNN_NN'",
                2 => "(CTTGAG................)",
                3 => 22,
                4 => 22,
                5 => -2,
                6 => 6,
            ],
            "BpuEI@" => [
                0 => "",
                1 => "",
                2 => "(................CTCAAG)",
                3 => 22,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "BsaI" => [
                0 => "BsaI,Bso31I,BspTNI,Eco31I",
                1 => "GGTCTCN'NNNN_",
                2 => "(GGTCTC.....)",
                3 => 11,
                4 => 7,
                5 => 4,
                6 => 6,
            ],
            "BsaI@" => [
                0 => "",
                1 => "",
                2 => "(.....GAGACC)",
                3 => 11,
                4 => 0,
                5 => 4,
                6 => 6,
            ],
            "BsaMI" => [
                0 => "BsaMI,BsmI,Mva1269I,PctI",
                1 => "GAATG_CN'",
                2 => "(GAATGC.)",
                3 => 7,
                4 => 7,
                5 => -2,
                6 => 6,
            ],
            "BsaMI@" => [
                0 => "",
                1 => "",
                2 => "(.GCATTC)",
                3 => 7,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "Bse1I" => [
                0 => "Bse1I,BseNI,BsrI,BsrSI",
                1 => "ACTG_GN'",
                2 => "(ACTGG.)",
                3 => 6,
                4 => 6,
                5 => -2,
                6 => 5,
            ],
            "Bse1I@" => [
                0 => "",
                1 => "",
                2 => "(.CCAGT)",
                3 => 6,
                4 => 2,
                5 => -2,
                6 => 5,
            ],
            "Bse3DI" => [
                0 => "Bse3DI,BseMI,BsrDI",
                1 => "GCAATG_NN'",
                2 => "(GCAATG..)",
                3 => 8,
                4 => 8,
                5 => -2,
                6 => 6,
            ],
            "Bse3DI@" => [
                0 => "",
                1 => "",
                2 => "(..CATTGC)",
                3 => 8,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "BseGI" => [
                0 => "BseGI,BstF5I",
                1 => "GGATG_NN'",
                2 => "(GGATG..)",
                3 => 7,
                4 => 7,
                5 => -2,
                6 => 5,
            ],
            "BseGI@" => [
                0 => "",
                1 => "",
                2 => "(..CATCC)",
                3 => 7,
                4 => 2,
                5 => -2,
                6 => 5,
            ],
            "BseMII" => [
                0 => "BseMII",
                1 => "CTCAGNNNNNNNN_NN'",
                2 => "(CTCAG..........)",
                3 => 15,
                4 => 15,
                5 => -2,
                6 => 5,
            ],
            "BseMII@" => [
                0 => "",
                1 => "",
                2 => "(..........CTGAG)",
                3 => 15,
                4 => 2,
                5 => -2,
                6 => 5,
            ],
            "BseRI" => [
                0 => "BseRI",
                1 => "GAGGAGNNNNNNNN_NN'",
                2 => "(GAGGAG..........)",
                3 => 16,
                4 => 16,
                5 => -2,
                6 => 6,
            ],
            "BseRI@" => [
                0 => "",
                1 => "",
                2 => "(..........CTCCTC)",
                3 => 16,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "BsgI" => [
                0 => "BsgI",
                1 => "GTGCAGNNNNNNNNNNNNNN_NN'",
                2 => "(GTGCAG................)",
                3 => 22,
                4 => 22,
                5 => -2,
                6 => 6,
            ],
            "BsgI@" => [
                0 => "",
                1 => "",
                2 => "(................CTGCAC)",
                3 => 22,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "BslFI" => [
                0 => "BslFI,FaqI",
                1 => "GGGACNNNNNNNNNN'NNNN_",
                2 => "(GGGAC..............)",
                3 => 19,
                4 => 15,
                5 => 4,
                6 => 5,
            ],
            "BslFI@" => [
                0 => "",
                1 => "",
                2 => "(..............GTCCC)",
                3 => 19,
                4 => 0,
                5 => 4,
                6 => 5,
            ],
            "BsmBI" => [
                0 => "BsmBI,Esp3I",
                1 => "CGTCTCN'NNNN_",
                2 => "(CGTCTC.....)",
                3 => 11,
                4 => 7,
                5 => 4,
                6 => 6,
            ],
            "BsmBI@" => [
                0 => "",
                1 => "",
                2 => "(.....GAGACG)",
                3 => 11,
                4 => 0,
                5 => 4,
                6 => 6,
            ],
            "BsmFI" => [
                0 => "BsmFI",
                1 => "GGGACNNNNNNNNNN'NNNN_",
                2 => "(GGGAC..............)",
                3 => 19,
                4 => 15,
                5 => 4,
                6 => 5,
            ],
            "BsmFI@" => [
                0 => "",
                1 => "",
                2 => "(..............GTCCC)",
                3 => 19,
                4 => 0,
                5 => 4,
                6 => 5,
            ],
            "BspCNI" => [
                0 => "BspCNI",
                1 => "CTCAGNNNNNNN_NN'",
                2 => "(CTCAG.........)",
                3 => 14,
                4 => 14,
                5 => -2,
                6 => 5,
            ],
            "BspCNI@" => [
                0 => "",
                1 => "",
                2 => "(.........CTGAG)",
                3 => 14,
                4 => 2,
                5 => -2,
                6 => 5,
            ],
            "BspQI" => [
                0 => "BspQI,LguI,PciSI,SapI",
                1 => "GCTCTTCN'NNN_",
                2 => "(GCTCTTC....)",
                3 => 11,
                4 => 8,
                5 => 3,
                6 => 7,
            ],
            "BspQI@" => [
                0 => "",
                1 => "",
                2 => "(....GAAGAGC)",
                3 => 11,
                4 => 0,
                5 => 3,
                6 => 7,
            ],
            "Bst6I" => [
                0 => "Bst6I,Eam1104I,EarI,Ksp632I",
                1 => "CTCTTCN'NNN_",
                2 => "(CTCTTC....)",
                3 => 10,
                4 => 7,
                5 => 3,
                6 => 6,
            ],
            "Bst6I@" => [
                0 => "",
                1 => "",
                2 => "(....GAAGAG)",
                3 => 10,
                4 => 0,
                5 => 3,
                6 => 6,
            ],
            "BtgZI" => [
                0 => "BtgZI",
                1 => "GCGATGNNNNNNNNNN'NNNN_",
                2 => "(GCGATG..............)",
                3 => 20,
                4 => 16,
                5 => 4,
                6 => 6,
            ],
            "BtgZI@" => [
                0 => "",
                1 => "",
                2 => "(..............CATCGC)",
                3 => 20,
                4 => 0,
                5 => 4,
                6 => 6,
            ],
            "BtsI" => [
                0 => "BtsI",
                1 => "GCAGTG_NN'",
                2 => "(GCAGTG..)",
                3 => 8,
                4 => 8,
                5 => -2,
                6 => 6,
            ],
            "BtsI@" => [
                0 => "",
                1 => "",
                2 => "(..CACTGC)",
                3 => 8,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "BtsIMutI" => [
                0 => "BtsIMutI",
                1 => "CAGTG_NN'",
                2 => "(CAGTG..)",
                3 => 7,
                4 => 7,
                5 => -2,
                6 => 5,
            ],
            "BtsIMutI@" => [
                0 => "",
                1 => "",
                2 => "(..CACTG)",
                3 => 7,
                4 => 2,
                5 => -2,
                6 => 5,
            ],
            "EciI" => [
                0 => "EciI",
                1 => "GGCGGANNNNNNNNN_NN'",
                2 => "(GGCGGA...........)",
                3 => 17,
                4 => 17,
                5 => -2,
                6 => 6,
            ],
            "EciI@" => [
                0 => "",
                1 => "",
                2 => "(...........TCCGCC)",
                3 => 17,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "Eco57MI" => [
                0 => "Eco57MI",
                1 => "CTGRAGNNNNNNNNNNNNNN_NN'",
                2 => "(CTGAAG................|CTGGAG................)",
                3 => 22,
                4 => 22,
                5 => -2,
                6 => 6,
            ],
            "Eco57MI@" => [
                0 => "",
                1 => "",
                2 => "(................CTCCAG|................CTTCAG)",
                3 => 22,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "EcoP15I" => [
                0 => "EcoP15I",
                1 => "CAGCAGNNNNNNNNNNNNNNNNNNNNNNNNN'NN_",
                2 => "(CAGCAG...........................)",
                3 => 33,
                4 => 31,
                5 => 2,
                6 => 6,
            ],
            "EcoP15I@" => [
                0 => "",
                1 => "",
                2 => "(...........................CTGCTG)",
                3 => 33,
                4 => 0,
                5 => 2,
                6 => 6,
            ],
            "FauI" => [
                0 => "FauI,SmuI",
                1 => "CCCGCNNNN'NN_",
                2 => "(CCCGC......)",
                3 => 11,
                4 => 9,
                5 => 2,
                6 => 5,
            ],
            "FauI@" => [
                0 => "",
                1 => "",
                2 => "(......GCGGG)",
                3 => 11,
                4 => 0,
                5 => 2,
                6 => 5,
            ],
            "FokI" => [
                0 => "FokI,BtsCI",
                1 => "GGATGNNNNNNNNN'NNNN_",
                2 => "(GGATG.............)",
                3 => 18,
                4 => 14,
                5 => 4,
                6 => 5,
            ],
            "FokI@" => [
                0 => "",
                1 => "",
                2 => "(.............CATCC)",
                3 => 18,
                4 => 0,
                5 => 4,
                6 => 5,
            ],
            "FspEI" => [
                0 => "FspEI",
                1 => "CCNNNNNNNNNNNN'NNNN_",
                2 => "(CC................)",
                3 => 18,
                4 => 14,
                5 => 4,
                6 => 2,
            ],
            "FspEI@" => [
                0 => "",
                1 => "",
                2 => "(................GG)",
                3 => 18,
                4 => 0,
                5 => 4,
                6 => 2,
            ],
            "HgaI" => [
                0 => "HgaI,CseI",
                1 => "GACGCNNNNN'NNNNN_",
                2 => "(GACGC..........)",
                3 => 15,
                4 => 10,
                5 => 5,
                6 => 5,
            ],
            "HgaI@" => [
                0 => "",
                1 => "",
                2 => "(..........GCGTC)",
                3 => 15,
                4 => 0,
                5 => 5,
                6 => 5,
            ],
            "HpyAV" => [
                0 => "HpyAV",
                1 => "GCTCTTCN'NNN_",
                2 => "(GACGC..........)",
                3 => 11,
                4 => 11,
                5 => -1,
                6 => 5,
            ],
            "HpyAV@" => [
                0 => "",
                1 => "",
                2 => "(......GAAGG)",
                3 => 11,
                4 => 1,
                5 => -1,
                6 => 5,
            ],
            "LpnPI" => [
                0 => "LpnPI",
                1 => "CCDGNNNNNNNNNN'NNNN_",
                2 => "(CCAG..............|CCGG..............|CCTG..............)",
                3 => 18,
                4 => 14,
                5 => 4,
                6 => 4,
            ],
            "LpnPI@" => [
                0 => "",
                1 => "",
                2 => "(..............CTGG|..............CCGG|..............CAGG)",
                3 => 18,
                4 => 0,
                5 => 4,
                6 => 4,
            ],
            "MboII" => [
                0 => "MboII",
                1 => "GAAGANNNNNNN_N'",
                2 => "(GAAGA........)",
                3 => 13,
                4 => 13,
                5 => -1,
                6 => 5,
            ],
            "MboII@" => [
                0 => "",
                1 => "",
                2 => "(........TCTTC)",
                3 => 13,
                4 => 1,
                5 => -1,
                6 => 5,
            ],
            "MlyI" => [
                0 => "MlyI,SchI",
                1 => "GAGTCNNNNN'",
                2 => "(GAGTC.....)",
                3 => 10,
                4 => 10,
                5 => 0,
                6 => 5,
            ],
            "MlyI@" => [
                0 => "",
                1 => "",
                2 => "(.....GACTC)",
                3 => 10,
                4 => 0,
                5 => 0,
                6 => 5,
            ],
            "MmeI" => [
                0 => "MmeI",
                1 => "TCCRACNNNNNNNNNNNNNNNNNN_NN'",
                2 => "(TCCAAC....................|TCCGAC....................)",
                3 => 26,
                4 => 26,
                5 => -2,
                6 => 6,
            ],
            "MmeI@" => [
                0 => "",
                1 => "",
                2 => "(....................GTCGGA|....................GTTGGA)",
                3 => 26,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "MnlI" => [
                0 => "MnlI",
                1 => "CCTCNNNNNN_N'",
                2 => "(CCTC.......)",
                3 => 11,
                4 => 11,
                5 => -1,
                6 => 4,
            ],
            "MnlI@" => [
                0 => "",
                1 => "",
                2 => "(.......GAGG)",
                3 => 11,
                4 => 1,
                5 => -1,
                6 => 4,
            ],
            "MspJI" => [
                0 => "MspJI",
                1 => "CNNRNNNNNNNNN'NNNN_",
                2 => "(C..A.............|C..G.............)",
                3 => 17,
                4 => 13,
                5 => 4,
                6 => 3,
            ],
            "MspJI@" => [
                0 => "",
                1 => "",
                2 => "(.............T..G|.............C..G)",
                3 => 17,
                4 => 0,
                5 => 4,
                6 => 3,
            ],
            "NmeAIII" => [
                0 => "NmeAIII",
                1 => "GCCGAGNNNNNNNNNNNNNNNNNNN_NN'",
                2 => "(GCCGAG.....................)",
                3 => 27,
                4 => 27,
                5 => -2,
                6 => 6,
            ],
            "NmeAIII@" => [
                0 => "",
                1 => "",
                2 => "(.....................CTCGGC)",
                3 => 27,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "PleI" => [
                0 => "",
                1 => "",
                2 => "(.....GACTC)",
                3 => 10,
                4 => 0,
                5 => 1,
                6 => 5,
            ],
            "SgeI" => [
                0 => "SgeI",
                1 => "CNNGNNNNNNNNN'NNNN_",
                2 => "(C..G.............)",
                3 => 17,
                4 => 13,
                5 => 4,
                6 => 2,
            ],
            "SgeI@" => [
                0 => "",
                1 => "",
                2 => "(.............C..G)",
                3 => 17,
                4 => 0,
                5 => 4,
                6 => 2,
            ],
            "TaqII" => [
                0 => "TaqII",
                1 => "GACCGANNNNNNNNN_NN' or CACCCANNNNNNNNN_NN'",
                2 => "(GACCGA...........|CACCCA...........)",
                3 => 17,
                4 => 17,
                5 => -2,
                6 => 6,
            ],
            "TaqII@" => [
                0 => "",
                1 => "",
                2 => "(...........TCGGTC|...........TGGGTG)",
                3 => 17,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "TsoI" => [
                0 => "TsoI",
                1 => "TARCCANNNNNNNNN_NN'",
                2 => "(TAACCA...........|TAGCCA...........)",
                3 => 17,
                4 => 17,
                5 => -2,
                6 => 6,
            ],
            "TsoI@" => [
                0 => "",
                1 => "",
                2 => "(...........TGGTTA|...........TGGCTA)",
                3 => 17,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "TspDTI" => [
                0 => "TspDTI",
                1 => "ATGAANNNNNNNNN_NN'",
                2 => "(ATGAA...........)",
                3 => 16,
                4 => 16,
                5 => -2,
                6 => 5,
            ],
            "TspDTI@" => [
                0 => "",
                1 => "",
                2 => "(...........TTCAT)",
                3 => 16,
                4 => 2,
                5 => -2,
                6 => 5,
            ],
            "TspGWI" => [
                0 => "TspGWI",
                1 => "ACGGANNNNNNNNN_NN'",
                2 => "(ACGGA...........)",
                3 => 16,
                4 => 16,
                5 => -2,
                6 => 5,
            ],
            "TspGWI@" => [
                0 => "",
                1 => "",
                2 => "(...........TCCGT)",
                3 => 16,
                4 => 2,
                5 => -2,
                6 => 5,
            ]
        ];

        $this->type2b = [
            "AjuI#" =>  [
                0 => "AjuI",
                1 => "_NNNNN'NNNNNNNGAANNNNNNNTTGGNNNNNN_NNNNN_'",
                2 => "(............GAA.......TTGG...........|...........CCAA.......TTC............)",
                3 => 37,
                4 => 5,
                5 => -5,
                6 => 7,
            ],
            "AlfI#" =>  [
                0 => "AjuI",
                1 => "_NN'NNNNNNNNNNCGANNNNNNTGCNNNNNNNNNN_NN'",
                2 => "(............CGA......TGC............|............GCA......TCG............)",
                3 => 36,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "AloI#" =>  [
                0 => "AloI",
                1 => "_NNNNN'NNNNNNNGAACNNNNNNTCCNNNNNNN_NNNNN'",
                2 => "(............GAAC......TCC............|............GGA......GTTC............)",
                3 => 37,
                4 => 5,
                5 => -5,
                6 => 7,
            ],
            "BaeI#" =>  [
                0 => "BaeI",
                1 => "_NNNNN'NNNNNNNNNNACNNNNGTAYCNNNNNNN_NNNNN'",
                2 => "(...............AC....GTACC............|...............AC....GTATC............|............GATAC....GT...............|............GGTAC....GT...............)",
                3 => 38,
                4 => 5,
                5 => -5,
                6 => 7,
            ],
            "BarI#" =>  [
                0 => "BarI",
                1 => "_NNNNN'NNNNNNNGAAGNNNNNNTACNNNNNNN_NNNNN'",
                2 => "(............GAAG......TAC............|............GTA......CTTC............)",
                3 => 37,
                4 => 5,
                5 => -5,
                6 => 7,
            ],
            "BcgI#" =>  [
                0 => "BcgI",
                1 => "_NN'NNNNNNNNNNCGANNNNNNTGCNNNNNNNNNN_NN'",
                2 => "(............CGA......TGC............|............GCA......TCG............)",
                3 => 36,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "BdaI#" =>  [
                0 => "BdaI",
                1 => "_NN'NNNNNNNNNNTGANNNNNNTCANNNNNNNNNN_NN'",
                2 => "(............TGA......TCA............)",
                3 => 36,
                4 => 2,
                5 => -2,
                6 => 6,
            ],
            "BplI#" =>  [
                0 => "BplI",
                1 => "_NNNNN'NNNNNNNNGAGNNNNNCTCNNNNNNNN_NNNNN'",
                2 => "(.............GAG.....CTC.............|.............GAG.....CTC.............)",
                3 => 37,
                4 => 5,
                5 => -5,
                6 => 6,
            ],
            "BsaXI#" =>  [
                0 => "BsaXI",
                1 => "_NNN'NNNNNNNNNACNNNNNCTCCNNNNNNN_NNN'",
                2 => "(............AC.....CTCC..........|..........GGAG.....GT............)",
                3 => 33,
                4 => 3,
                5 => -3,
                6 => 6,
            ],
            "CspCI#" =>  [
                0 => "CspCI",
                1 => "_NN'NNNNNNNNNNNCAANNNNNGTGGNNNNNNNNNN_NN'",
                2 => "(.............CAA.....GTGG............|............GCA.....TCG.............)",
                3 => 37,
                4 => 2,
                5 => -2,
                6 => 7,
            ],
            "FalI#" =>  [
                0 => "FalI",
                1 => "_NNNNN'NNNNNNNNAAGNNNNNCTTNNNNNNNN_NNNNN'",
                2 => "(.............AAG.....CTT.............|.............AAG.....CTT.............)",
                3 => 37,
                4 => 5,
                5 => -5,
                6 => 6,
            ],
            "Hin4I#" =>  [
                0 => "Hin4I",
                1 => "_NNNNN'NNNNNNNNGAYNNNNNVTCNNNNNNNN_NNNNN'",
                2 => "(.............GAC.....ATC.............|.............GAC.....CTC.............|.............GAC.....GTC.............|.............GAT.....ATC.............|.............GAT.....CTC.............|.............GAT.....GTC.............|.............GAG.....ATC.............|.............GAG.....ATC.............)",
                3 => 37,
                4 => 5,
                5 => -5,
                6 => 6,
            ],
            "PpiI#" =>  [
                0 => "PpiI",
                1 => "_NNNNN'NNNNNNNGAACNNNNNCTCNNNNNNNN_NNNNN'",
                2 => "(............GAAC.....CTC.............|.............GAG.....GTTC............)",
                3 => 37,
                4 => 5,
                5 => -5,
                6 => 7,
            ],
            "PsrI#" =>  [
                0 => "PsrI",
                1 => "_NNNNN'NNNNNNNGAACNNNNNNTACNNNNNNN_NNNNN'",
                2 => "(............GAAC......TAC............|............GTA......GTTC............)",
                3 => 37,
                4 => 5,
                5 => -5,
                6 => 7,
            ],
            "TstI#" =>  [
                0 => "TstI",
                1 => "_NNNNN'NNNNNNNNCACNNNNNNTCCNNNNNNN_NNNNN'",
                2 => "(.............CAC......TCC............|............GGA......GTG.............)",
                3 => 37,
                4 => 5,
                5 => -5,
                6 => 6,
            ]
        ];

        $vendors = [
            "AanI" => "F",
            "AarI" => "F",
            "AasI" => "F",
            "AatII" => "FIKMNR",
            "AbaSI" => "N",
            "AbsI" => "I",
            "Acc16I" => "I",
            "Acc36I" => "I",
            "Acc65I" => "FINR",
            "AccB1I" => "I",
            "AccB7I" => "I",
            "AccBSI" => "I",
            "AccI" => "BJKMNQRSUX",
            "AccII" => "JK",
            "AccIII" => "JKR",
            "AciI" => "N",
            "AclI" => "IN",
            "AclWI" => "I",
            "AcoI" => "I",
            "AcsI" => "I",
            "AcuI" => "IN",
            "AcvI" => "QX",
            "AcyI" => "J",
            "AdeI" => "F",
            "AfaI" => "BK",
            "AfeI" => "IN",
            "AfiI" => "V",
            "AflII" => "JKN",
            "AflIII" => "MN",
            "AgeI" => "JNR",
            "AgsI" => "I",
            "AhdI" => "N",
            "AhlI" => "IV",
            "AjiI" => "F",
            "AjnI" => "I",
            "AjuI" => "F",
            "AleI" => "N",
            "AlfI" => "F",
            "AloI" => "F",
            "AluBI" => "I",
            "AluI" => "BCFIJKMNOQRSUVXY",
            "Alw21I" => "F",
            "Alw26I" => "F",
            "Alw44I" => "FJ",
            "AlwI" => "N",
            "AlwNI" => "N",
            "Ama87I" => "IV",
            "Aor13HI" => "K",
            "Aor51HI" => "K",
            "ApaI" => "BFIJKMNQRSUVX",
            "ApaLI" => "CKNU",
            "ApeKI" => "N",
            "ApoI" => "N",
            "ArsI" => "I",
            "AscI" => "N",
            "AseI" => "JNO",
            "AsiGI" => "IV",
            "AsiSI" => "IN",
            "Asp700I" => "M",
            "Asp718I" => "M",
            "AspA2I" => "IV",
            "AspLEI" => "IV",
            "AspS9I" => "IV",
            "AssI" => "U",
            "AsuC2I" => "I",
            "AsuHPI" => "IV",
            "AsuII" => "C",
            "AsuNHI" => "IV",
            "AvaI" => "JNQRUX",
            "AvaII" => "JNRXY",
            "AvrII" => "N",
            "AxyI" => "J",
            "BaeGI" => "N",
            "BaeI" => "N",
            "BalI" => "BJKQRX",
            "BamHI" => "BCFIJKMNOQRSUVXY",
            "BanI" => "NRU",
            "BanII" => "KNX",
            "BarI" => "I",
            "BasI" => "U",
            "BauI" => "F",
            "BbrPI" => "M",
            "BbsI" => "N",
            "Bbv12I" => "IV",
            "BbvCI" => "N",
            "BbvI" => "N",
            "BccI" => "N",
            "BceAI" => "N",
            "BcgI" => "N",
            "BciT130I" => "K",
            "BciVI" => "N",
            "BclI" => "CFJMNORSUY",
            "BcnI" => "FK",
            "BcoDI" => "N",
            "BcuI" => "F",
            "BfaI" => "N",
            "BfmI" => "F",
            "BfoI" => "F",
            "BfrI" => "M",
            "BfuAI" => "N",
            "BfuCI" => "N",
            "BfuI" => "F",
            "BglI" => "CFIJKNOQRUVXY",
            "BglII" => "BCFIJKMNOQRSUXY",
            "BisI" => "I",
            "BlnI" => "KMS",
            "BlpI" => "N",
            "BlsI" => "I",
            "BmcAI" => "V",
            "Bme1390I" => "F",
            "Bme18I" => "IV",
            "BmeRI" => "V",
            "BmeT110I" => "BK",
            "BmgBI" => "N",
            "BmgT120I" => "K",
            "BmiI" => "V",
            "BmrFI" => "V",
            "BmrI" => "N",
            "BmsI" => "F",
            "BmtI" => "INV",
            "BmuI" => "I",
            "BoxI" => "F",
            "BpiI" => "F",
            "BplI" => "F",
            "BpmI" => "IN",
            "Bpu10I" => "FINV",
            "Bpu1102I" => "FK",
            "Bpu14I" => "IV",
            "BpuEI" => "N",
            "BpuMI" => "V",
            "BpvUI" => "V",
            "Bsa29I" => "I",
            "BsaAI" => "N",
            "BsaBI" => "N",
            "BsaHI" => "N",
            "BsaI" => "N",
            "BsaJI" => "N",
            "BsaWI" => "N",
            "BsaXI" => "N",
            "Bsc4I" => "I",
            "Bse118I" => "IV",
            "Bse1I" => "IV",
            "Bse21I" => "IV",
            "Bse3DI" => "IV",
            "Bse8I" => "IV",
            "BseAI" => "CM",
            "BseBI" => "C",
            "BseCI" => "C",
            "BseDI" => "F",
            "BseGI" => "F",
            "BseJI" => "F",
            "BseLI" => "F",
            "BseMI" => "F",
            "BseMII" => "F",
            "BseNI" => "F",
            "BsePI" => "IV",
            "BseRI" => "N",
            "BseSI" => "F",
            "BseX3I" => "IV",
            "BseXI" => "F",
            "BseYI" => "N",
            "BsgI" => "N",
            "Bsh1236I" => "F",
            "Bsh1285I" => "F",
            "BshFI" => "C",
            "BshNI" => "F",
            "BshTI" => "BF",
            "BshVI" => "V",
            "BsiEI" => "N",
            "BsiHKAI" => "N",
            "BsiHKCI" => "QX",
            "BsiSI" => "C",
            "BsiWI" => "N",
            "BslFI" => "I",
            "BslI" => "N",
            "BsmAI" => "N",
            "BsmBI" => "N",
            "BsmFI" => "N",
            "BsmI" => "JMNS",
            "BsnI" => "V",
            "Bso31I" => "IV",
            "BsoBI" => "N",
            "Bsp119I" => "F",
            "Bsp120I" => "F",
            "Bsp1286I" => "JKN",
            "Bsp13I" => "IV",
            "Bsp1407I" => "FK",
            "Bsp143I" => "F",
            "Bsp1720I" => "IV",
            "Bsp19I" => "IV",
            "Bsp68I" => "F",
            "BspACI" => "I",
            "BspCNI" => "N",
            "BspDI" => "N",
            "BspEI" => "N",
            "BspFNI" => "I",
            "BspHI" => "N",
            "BspLI" => "F",
            "BspMI" => "N",
            "BspOI" => "F",
            "BspPI" => "F",
            "BspQI" => "N",
            "BspT104I" => "K",
            "BspT107I" => "K",
            "BspTI" => "F",
            "BsrBI" => "N",
            "BsrDI" => "N",
            "BsrFI" => "N",
            "BsrGI" => "N",
            "BsrI" => "N",
            "BsrSI" => "R",
            "BssAI" => "C",
            "BssECI" => "I",
            "BssHII" => "JKMNQRSX",
            "BssKI" => "N",
            "BssMI" => "V",
            "BssNAI" => "IV",
            "BssNI" => "V",
            "BssSI" => "N",
            "BssT1I" => "IV",
            "Bst1107I" => "FK",
            "Bst2BI" => "IV",
            "Bst2UI" => "IV",
            "Bst4CI" => "IV",
            "Bst6I" => "IV",
            "BstACI" => "I",
            "BstAFI" => "I",
            "BstAPI" => "IN",
            "BstAUI" => "IV",
            "BstBAI" => "IV",
            "BstBI" => "N",
            "BstC8I" => "I",
            "BstDEI" => "IV",
            "BstDSI" => "IV",
            "BstEII" => "CJNRSU",
            "BstENI" => "IV",
            "BstF5I" => "IV",
            "BstFNI" => "IV",
            "BstH2I" => "IV",
            "BstHHI" => "IV",
            "BstKTI" => "I",
            "BstMAI" => "IV",
            "BstMBI" => "IV",
            "BstMCI" => "IV",
            "BstMWI" => "I",
            "BstNI" => "N",
            "BstNSI" => "I",
            "BstOI" => "R",
            "BstPAI" => "IV",
            "BstPI" => "K",
            "BstSCI" => "I",
            "BstSFI" => "I",
            "BstSLI" => "I",
            "BstSNI" => "IV",
            "BstUI" => "N",
            "BstV1I" => "I",
            "BstV2I" => "IV",
            "BstX2I" => "IV",
            "BstXI" => "FIJKMNQRVX",
            "BstYI" => "N",
            "BstZ17I" => "N",
            "BstZI" => "R",
            "Bsu15I" => "F",
            "Bsu36I" => "NR",
            "BsuI" => "I",
            "BsuRI" => "FI",
            "BtgI" => "N",
            "BtgZI" => "N",
            "BtrI" => "I",
            "BtsCI" => "N",
            "BtsI" => "N",
            "BtsIMutI" => "N",
            "BtuMI" => "V",
            "BveI" => "F",
            "Cac8I" => "N",
            "CaiI" => "F",
            "CciI" => "I",
            "CciNI" => "IV",
            "CfoI" => "MRS",
            "Cfr10I" => "FK",
            "Cfr13I" => "F",
            "Cfr42I" => "F",
            "Cfr9I" => "F",
            "ClaI" => "BKMNQRSUX",
            "CpoI" => "FK",
            "CseI" => "F",
            "CsiI" => "F",
            "Csp6I" => "F",
            "CspAI" => "C",
            "CspCI" => "N",
            "CspI" => "R",
            "CviAII" => "N",
            "CviJI" => "QX",
            "CviKI-1" => "N",
            "CviQI" => "N",
            "DdeI" => "KMNOQRSX",
            "DinI" => "V",
            "DpnI" => "BEFKMNOQRSX",
            "DpnII" => "N",
            "DraI" => "BFIJKMNQRSUVXY",
            "DraIII" => "IMNV",
            "DrdI" => "N",
            "DriI" => "I",
            "DseDI" => "IV",
            "EaeI" => "KN",
            "EagI" => "N",
            "Eam1104I" => "F",
            "Eam1105I" => "FK",
            "EarI" => "N",
            "EciI" => "N",
            "Ecl136II" => "F",
            "EclXI" => "MS",
            "Eco105I" => "F",
            "Eco130I" => "F",
            "Eco147I" => "F",
            "Eco24I" => "F",
            "Eco31I" => "F",
            "Eco32I" => "F",
            "Eco47I" => "F",
            "Eco47III" => "FMR",
            "Eco52I" => "FK",
            "Eco53kI" => "N",
            "Eco57I" => "F",
            "Eco72I" => "F",
            "Eco81I" => "FK",
            "Eco88I" => "F",
            "Eco91I" => "F",
            "EcoICRI" => "IRV",
            "EcoNI" => "N",
            "EcoO109I" => "FJKN",
            "EcoO65I" => "K",
            "EcoP15I" => "N",
            "EcoRI" => "BCFIJKMNOQRSUVXY",
            "EcoRII" => "FJ",
            "EcoRV" => "BCIJKMNOQRSUVXY",
            "EcoT14I" => "K",
            "EcoT22I" => "BK",
            "EcoT38I" => "J",
            "EgeI" => "I",
            "EheI" => "F",
            "ErhI" => "IV",
            "Esp3I" => "F",
            "FaeI" => "I",
            "FaiI" => "I",
            "FalI" => "I",
            "FaqI" => "F",
            "FatI" => "IN",
            "FauI" => "IN",
            "FauNDI" => "IV",
            "FbaI" => "K",
            "FblI" => "IV",
            "Fnu4HI" => "N",
            "FokI" => "IJKMNVX",
            "FriOI" => "IV",
            "FseI" => "N",
            "Fsp4HI" => "I",
            "FspAI" => "F",
            "FspBI" => "F",
            "FspEI" => "N",
            "FspI" => "JN",
            "GlaI" => "I",
            "GluI" => "I",
            "GsaI" => "I",
            "GsuI" => "F",
            "HaeII" => "JKNR",
            "HaeIII" => "BIJKMNOQRSUXY",
            "HapII" => "BK",
            "HgaI" => "IN",
            "HhaI" => "BFJKNQRUXY",
            "Hin1I" => "FK",
            "Hin1II" => "F",
            "Hin6I" => "F",
            "HincII" => "BFJKNOQRUXY",
            "HindII" => "IMV",
            "HindIII" => "BCFIJKMNOQRSUVXY",
            "HinfI" => "BCFIJKMNOQRUVXY",
            "HinP1I" => "N",
            "HpaI" => "BCIJKMNQRSUVX",
            "HpaII" => "FINQRSUVX",
            "HphI" => "FN",
            "Hpy166II" => "N",
            "Hpy188I" => "N",
            "Hpy188III" => "N",
            "Hpy8I" => "F",
            "Hpy99I" => "N",
            "HpyAV" => "N",
            "HpyCH4III" => "N",
            "HpyCH4IV" => "N",
            "HpyCH4V" => "N",
            "HpyF10VI" => "F",
            "HpyF3I" => "F",
            "HpySE526I" => "I",
            "Hsp92I" => "R",
            "Hsp92II" => "R",
            "HspAI" => "IV",
            "KasI" => "N",
            "KflI" => "F",
            "Kpn2I" => "F",
            "KpnI" => "BCFIJKMNOQRSUVXY",
            "KroI" => "I",
            "Ksp22I" => "IV",
            "KspAI" => "F",
            "KspI" => "MS",
            "Kzo9I" => "I",
            "LguI" => "F",
            "LpnPI" => "N",
            "Lsp1109I" => "F",
            "LweI" => "F",
            "MabI" => "I",
            "MaeI" => "M",
            "MaeII" => "M",
            "MaeIII" => "M",
            "MalI" => "I",
            "MauBI" => "F",
            "MbiI" => "F",
            "MboI" => "BCFKNQRUXY",
            "MboII" => "FIJKNQRVX",
            "MfeI" => "IN",
            "MflI" => "K",
            "MhlI" => "IV",
            "MlsI" => "F",
            "MluCI" => "N",
            "MluI" => "BFIJKMNOQRUVX",
            "MluNI" => "M",
            "Mly113I" => "I",
            "MlyI" => "N",
            "MmeI" => "N",
            "MnlI" => "FINQVX",
            "Mph1103I" => "F",
            "MreI" => "F",
            "MroI" => "MO",
            "MroNI" => "IV",
            "MroXI" => "IV",
            "MscI" => "NO",
            "MseI" => "BN",
            "MslI" => "N",
            "Msp20I" => "IV",
            "MspA1I" => "INRV",
            "MspCI" => "C",
            "MspI" => "FIJKNQRSUVXY",
            "MspJI" => "N",
            "MspR9I" => "I",
            "MssI" => "F",
            "MunI" => "FKM",
            "Mva1269I" => "F",
            "MvaI" => "FMS",
            "MvnI" => "M",
            "MvrI" => "U",
            "MwoI" => "N",
            "NaeI" => "CKNU",
            "NarI" => "JMNQRUX",
            "NciI" => "JNR",
            "NcoI" => "BCFJKMNOQRSUXY",
            "NdeI" => "BFJKMNQRSUXY",
            "NdeII" => "JM",
            "NgoMIV" => "N",
            "NheI" => "BCFJKMNOQRSUX",
            "NlaIII" => "N",
            "NlaIV" => "N",
            "NmeAIII" => "N",
            "NmuCI" => "F",
            "NotI" => "BCFJKMNOQRSUXY",
            "NruI" => "BCIJKMNQRUX",
            "NsbI" => "FK",
            "NsiI" => "JMNQRSUX",
            "NspI" => "N",
            "NspV" => "J",
            "OliI" => "F",
            "PacI" => "FNO",
            "PaeI" => "F",
            "PaeR7I" => "N",
            "PagI" => "F",
            "PalAI" => "I",
            "PasI" => "F",
            "PauI" => "F",
            "PceI" => "IV",
            "PciI" => "IN",
            "PciSI" => "I",
            "PcsI" => "I",
            "PctI" => "IV",
            "PdiI" => "F",
            "PdmI" => "F",
            "PfeI" => "F",
            "Pfl23II" => "F",
            "PflFI" => "N",
            "PflMI" => "N",
            "PfoI" => "F",
            "PinAI" => "MQX",
            "Ple19I" => "I",
            "PleI" => "N",
            "PluTI" => "N",
            "PmaCI" => "K",
            "PmeI" => "N",
            "PmlI" => "N",
            "PpsI" => "I",
            "Ppu21I" => "F",
            "PpuMI" => "N",
            "PscI" => "F",
            "PshAI" => "KN",
            "PshBI" => "K",
            "PsiI" => "IN",
            "Psp124BI" => "IV",
            "Psp1406I" => "FK",
            "Psp5II" => "F",
            "Psp6I" => "I",
            "PspCI" => "IV",
            "PspEI" => "IV",
            "PspGI" => "N",
            "PspLI" => "I",
            "PspN4I" => "I",
            "PspOMI" => "INV",
            "PspPI" => "C",
            "PspPPI" => "I",
            "PspXI" => "IN",
            "PsrI" => "I",
            "PstI" => "BCFIJKMNOQRSUVXY",
            "PstNI" => "I",
            "PsuI" => "F",
            "PsyI" => "F",
            "PteI" => "F",
            "PvuI" => "BFKMNOQRSUXY",
            "PvuII" => "BCFIJKMNOQRSUXY",
            "RgaI" => "I",
            "RigI" => "I",
            "RruI" => "F",
            "RsaI" => "CFIJMNQRSVXY",
            "RsaNI" => "I",
            "RseI" => "F",
            "Rsr2I" => "IV",
            "RsrII" => "NQX",
            "SacI" => "BFJKMNOQRSUX",
            "SacII" => "BJKNOQRX",
            "SalI" => "BCFIJKMNOQRSUVXY",
            "SapI" => "N",
            "SaqAI" => "F",
            "SatI" => "F",
            "Sau3AI" => "CJKMNRSU",
            "Sau96I" => "JNU",
            "SbfI" => "INV",
            "ScaI" => "BCFJKMNOQRSX",
            "SchI" => "F",
            "ScrFI" => "JN",
            "SdaI" => "F",
            "SduI" => "F",
            "SetI" => "I",
            "SexAI" => "MN",
            "SfaAI" => "F",
            "SfaNI" => "INV",
            "SfcI" => "N",
            "SfiI" => "CFIJKMNOQRSUVX",
            "SfoI" => "N",
            "Sfr274I" => "IV",
            "Sfr303I" => "IV",
            "SfuI" => "M",
            "SgeI" => "F",
            "SgfI" => "R",
            "SgrAI" => "N",
            "SgrBI" => "C",
            "SgrDI" => "F",
            "SgsI" => "F",
            "SlaI" => "C",
            "SmaI" => "BCFIJKMNOQRSUVXY",
            "SmiI" => "FIKV",
            "SmiMI" => "IV",
            "SmlI" => "N",
            "SmoI" => "F",
            "SnaBI" => "CKMNRU",
            "SpeI" => "BJKMNOQRSUX",
            "SphI" => "BCIJKMNOQRSVX",
            "Sse8387I" => "K",
            "Sse9I" => "IV",
            "SseBI" => "C",
            "SsiI" => "F",
            "SspDI" => "F",
            "SspI" => "BCFIJKMNQRSUVX",
            "SstI" => "C",
            "StrI" => "U",
            "StuI" => "BJKMNQRUX",
            "StyD4I" => "N",
            "StyI" => "CJN",
            "SwaI" => "JMN",
            "TaaI" => "F",
            "TaiI" => "F",
            "TaqI" => "BCFIJKMNQRSUVXY",
            "TaqII" => "QX",
            "TasI" => "F",
            "TatI" => "F",
            "TauI" => "F",
            "TfiI" => "N",
            "Tru1I" => "F",
            "Tru9I" => "IMRV",
            "TscAI" => "F",
            "TseFI" => "I",
            "TseI" => "N",
            "Tsp45I" => "N",
            "TspDTI" => "QX",
            "TspGWI" => "QX",
            "TspMI" => "N",
            "TspRI" => "N",
            "Tth111I" => "IKNQVX",
            "Van91I" => "FK",
            "Vha464I" => "V",
            "VneI" => "IV",
            "VpaK11BI" => "K",
            "VspI" => "FIRV",
            "XagI" => "F",
            "XapI" => "F",
            "XbaI" => "BCFIJKMNOQRSUVXY",
            "XceI" => "F",
            "XcmI" => "N",
            "XhoI" => "BFJKMNOQRSUXY",
            "XhoII" => "R",
            "XmaI" => "INRUV",
            "XmaJI" => "F",
            "XmiI" => "F",
            "XmnI" => "NRU",
            "XspI" => "K",
            "ZraI" => "INV",
            "ZrmI" => "I",
            "Zsp2I" => "IV",
        ];

        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiMock = $this->getMockBuilder('AppBundle\Bioapi\Bioapi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(["getVendorLinks","getTypeIIEndonucleases","getTypeIIsEndonucleases","getTypeIIbEndonucleases","getVendors"])
            ->getMock();
        $this->apiMock->method("getVendorLinks")->will($this->returnValue($vendorLinks));
        $this->apiMock->method("getTypeIIEndonucleases")->will($this->returnValue($this->aType2));
        $this->apiMock->method("getTypeIIsEndonucleases")->will($this->returnValue($this->type2s));
        $this->apiMock->method("getTypeIIbEndonucleases")->will($this->returnValue($this->type2b));
        $this->apiMock->method("getVendors")->will($this->returnValue($vendors));
    }

    public function testGetVendors()
    {
        $message = "";
        $enzyme = "BfaI,FspBI,MaeI,XspI";
        $sExpected = [
          "BfaI" => [
            "company" => [
              "name" => "N",
              "url" => "http://rebase.neb.com/rebase/enz/BfaI.html",
            ],
            "links" => [
              0 => [
                "name" => "New England Biolabs",
                "url" => "http://www.neb.com",
              ]
            ]
          ],
          "FspBI" => [
            "company" => [
              "name" => "F",
              "url" => "http://rebase.neb.com/rebase/enz/FspBI.html",
            ],
            "links" => [
              0 => [
                "name" => "Fermentas AB",
                "url" => "http://www.fermentas.com",
              ]
            ]
          ],
          "MaeI" => [
            "company" => [
              "name" => "M",
              "url" => "http://rebase.neb.com/rebase/enz/MaeI.html",
            ],
            "links" => [
              0 => [
                "name" => "Roche Applied Science",
                "url" => "http://www.roche.com",
              ]
            ]
          ],
          "XspI" => [
            "company" => [
              "name" => "K",
              "url" => "http://rebase.neb.com/rebase/enz/XspI.html",
            ],
            "links" => [
              0 => [
                "name" => "Takara Shuzo Co. Ltd.",
                "url" => "http://www.takarashuzo.co.jp/english/index.htm",
              ]
            ]
          ]
        ];

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->getVendors($message, $enzyme);

        $this->assertEquals($sExpected, $testFunction);
    }

    public function testGetNucleolasesInfosOnlyType2()
    {
        $bIIs = false;
        $bIIb = false;
        $bDefined = true;

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->getNucleolasesInfos($bIIs, $bIIb, $bDefined);

        $this->assertEquals($this->aType2, $testFunction);
    }

    public function testGetNucleolasesInfosType2AndType2b()
    {
        $bIIs = false;
        $bIIb = true;
        $bDefined = false;

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->getNucleolasesInfos($bIIs, $bIIb, $bDefined);

        $aExpected = array_merge($this->aType2, $this->type2b);

        $this->assertEquals($aExpected, $testFunction);
    }

    public function testGetNucleolasesInfosType2AndType2s()
    {
        $bIIs = true;
        $bIIb = false;
        $bDefined = false;

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->getNucleolasesInfos($bIIs, $bIIb, $bDefined);

        $aExpected = array_merge($this->aType2, $this->type2s);

        $this->assertEquals($aExpected, $testFunction);
    }

    public function testGetNucleolasesInfosType2AndType2bAndType2s()
    {
        $bIIs = true;
        $bIIb = true;
        $bDefined = false;

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->getNucleolasesInfos($bIIs, $bIIb, $bDefined);

        $aExpected = array_merge($this->aType2, $this->type2b);
        $aExpected = array_merge($aExpected, $this->type2s);

        $this->assertEquals($aExpected, $testFunction);
    }

    public function testReduceEnzymesArray()
    {
        $aEnzymes = $this->aType2;
        $iMinimum = 3;
        $iRetype = 1;
        $bDefinedSq = false;
        $sWre = "AarI";
        $aExpected = [
          "AatI" =>  [
            0 => "AatI,Eco147I,PceI,SseBI,StuI",
            1 => "AGG'CCT",
            2 => "(AGGCCT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Acc16I" =>  [
            0 => "Acc16I,AviII,FspI,NsbI",
            1 => "TGC'GCA",
            2 => "(TGCGCA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AccBSI" =>  [
            0 => "AccBSI,BsrBI,MbiI",
            1 => "CCG'CTC",
            2 => "(CCGCTC|GAGCGG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AccII" =>  [
            0 => "AccII,Bsh1236I,BspFNI,BstFNI,BstUI,MvnI",
            1 => "CG'CG",
            2 => "(CGCG)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "AcvI" =>  [
            0 => "AcvI,BbrPI,Eco72I,PmaCI,PmlI,PspCI",
            1 => "CAC'GTG",
            2 => "(CACGTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AfaI" =>  [
            0 => "AfaI,RsaI",
            1 => "GT'AC",
            2 => "(GTAC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "AfeI" =>  [
            0 => "AfeI,Aor51HI,Eco47III",
            1 => "AGC'GCT",
            2 => "(AGCGCT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AjiI" =>  [
            0 => "AjiI,BmgBI,BtrI",
            1 => "CAC'GTC",
            2 => "(CACGTC|GACGTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AleI" =>  [
            0 => "AleI,OliI",
            1 => "CACNN'NNGTG",
            2 => "(CAC....GTG)",
            3 => 10,
            4 => 5,
            5 => 0,
            6 => 6,
          ],
          "AluI" =>  [
            0 => "AluI,AluBI",
            1 => "AG'CT",
            2 => "(AGCT)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "Asp700I" =>  [
            0 => "Asp700I,MroXI,PdmI,XmnI",
            1 => "GAANN'NNTTC",
            2 => "(GAA....TTC)",
            3 => 10,
            4 => 5,
            5 => 0,
            6 => 6,
          ],
          "AssI" =>  [
            0 => "AssI,BmcAI,ScaI,ZrmI",
            1 => "AGT'ACT",
            2 => "(AGTACT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BalI" =>  [
            0 => "BalI,MlsI,MluNI,MscI,Msp20I",
            1 => "TGG'CCA",
            2 => "(TGGCCA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BmiI" =>  [
            0 => "BmiI,BspLI,NlaIV,PspN4I",
            1 => "GGN'NCC",
            2 => "(GG..CC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 4,
          ],
          "BoxI" =>  [
            0 => "BoxI,PshAI,BstPAI",
            1 => "GACNN'NNGTC",
            2 => "(GAC....GTC)",
            3 => 10,
            4 => 5,
            5 => 0,
            6 => 6,
          ],
          "BsaAI" =>  [
            0 => "BsaAI,BstBAI,Ppu21I",
            1 => "YAC'GTR",
            2 => "(CACGTA|CACGTG|TACGTA|TACGTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BsaBI" =>  [
            0 => "BsaBI,Bse8I,BseJI,MamI",
            1 => "GATNN'NNATC",
            2 => "(GAT....ATC)",
            3 => 10,
            4 => 5,
            5 => 0,
            6 => 6,
          ],
          "BshFI" =>  [
            0 => "BshFI,BsnI,BspANI,BsuRI,HaeIII,PhoI",
            1 => "GG'CC",
            2 => "(GGCC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "Bsp68I" =>  [
            0 => "Bsp68I,BtuMI,NruI,RruI",
            1 => "TCG'CGA",
            2 => "(TCGCGA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BssNAI" =>  [
            0 => "BssNAI,Bst1107I,BstZ17I",
            1 => "GTA'TAC",
            2 => "(GTATAC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BstC8I" =>  [
            0 => "BstC8I,Cac8I",
            1 => "GCN'NGC",
            2 => "(GC..GC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 4,
          ],
          "BstSNI" =>  [
            0 => "BstSNI,Eco105I,SnaBI",
            1 => "TAC'GTA",
            2 => "(TACGTA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "CviJI" =>  [
            0 => "CviJI,CviKI-1",
            1 => "RG'CY",
            2 => "(AGCC|AGCT|GGCC|GGCT)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "DpnI" =>  [
            0 => "DpnI,MalI",
            1 => "GA'TC",
            2 => "(GATC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "DraI" =>  [
            0 => "DraI",
            1 => "TTT'AAA",
            2 => "(TTTAAA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Ecl136II" =>  [
            0 => "Ecl136II,Eco53kI,EcoICRI",
            1 => "GAG'CTC",
            2 => "(GAGCTC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Eco32I" =>  [
            0 => "Eco32I,EcoRV",
            1 => "GAT'ATC",
            2 => "(GATATC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "EgeI" =>  [
            0 => "EgeI,EheI,SfoI",
            1 => "GGC'GCC",
            2 => "(GGCGCC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "FaiI" =>  [
            0 => "FaiI",
            1 => "YA'TR",
            2 => "(CATA|CATG|TATA|TATG)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "FspAI" =>  [
            0 => "FspAI",
            1 => "RTGC'GCAY",
            2 => "(ATGCGCAC|ATGCGCAT|GTGCGCAC|GTGCGCAT)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "GlaI" =>  [
            0 => "GlaI",
            1 => "GC'GC",
            2 => "(GCGC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "HincII" =>  [
            0 => "HincII,HindII",
            1 => "GTY'RAC",
            2 => "(GTCAAC|GTCGAC|GTTAAC|GTTGAC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "HpaI" =>  [
            0 => "HpaI,KspAI",
            1 => "GTT'AAC",
            2 => "(GTTAAC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Hpy166II" =>  [
            0 => "Hpy166II,Hpy8I",
            1 => "GTN'NAC",
            2 => "(GT..AC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 4,
          ],
          "HpyCH4V" =>  [
            0 => "HpyCH4V",
            1 => "TG'CA",
            2 => "(TGCA)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "MslI" =>  [
            0 => "MslI,RseI,SmiMI",
            1 => "CAYNN'NNRTG",
            2 => "(CAC....ATG|CAC....GTG|CAT....ATG|CAT....GTG)",
            3 => 10,
            4 => 5,
            5 => 0,
            6 => 6,
          ],
          "MspA1I" =>  [
            0 => "MspA1I",
            1 => "CMG'CKG",
            2 => "(CAGCGG|CAGCTG|CCGCGG|CCGCTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "MssI" =>  [
            0 => "MssI,PmeI",
            1 => "GTTT'AAAC",
            2 => "(GTTTAAAC)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "NaeI" =>  [
            0 => "NaeI,PdiI",
            1 => "GCC'GGC",
            2 => "(GCCGGC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "PsiI" =>  [
            0 => "AanI,PsiI",
            1 => "TTA'TAA",
            2 => "(TTATAA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "PvuII" =>  [
            0 => "PvuII",
            1 => "CAG'CTG",
            2 => "(CAGCTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "SmaI" =>  [
            0 => "SmaI",
            1 => "CCC'GGG",
            2 => "(CCCGGG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "SmiI" =>  [
            0 => "SmiI,SwaI",
            1 => "ATTT'AAAT",
            2 => "(ATTTAAAT)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "SrfI" =>  [
            0 => "SrfI",
            1 => "GCCC'GGGC",
            2 => "(GCCCGGGC)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "SspI" =>  [
            0 => "SspI",
            1 => "AAT'ATT",
            2 => "(AATATT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "ZraI" =>  [
            0 => "ZraI",
            1 => "GAC'GTC",
            2 => "(GACGTC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ]
        ];

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->reduceEnzymesArray($aEnzymes, $iMinimum, $iRetype, $bDefinedSq, $sWre);
        $this->assertEquals($aExpected, $testFunction);
    }

    public function testReduceEnzymesArrayWreNull()
    {
        $aEnzymes = $this->aType2;
        $iMinimum = 8;
        $iRetype = 1;
        $bDefinedSq = false;
        $sWre = "";
        $aExpected = [
          "FspAI" => [
            0 => "FspAI",
            1 => "RTGC'GCAY",
            2 => "(ATGCGCAC|ATGCGCAT|GTGCGCAC|GTGCGCAT)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "MssI" => [
            0 => "MssI,PmeI",
            1 => "GTTT'AAAC",
            2 => "(GTTTAAAC)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "SmiI" => [
            0 => "SmiI,SwaI",
            1 => "ATTT'AAAT",
            2 => "(ATTTAAAT)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "SrfI" => [
            0 => "SrfI",
            1 => "GCCC'GGGC",
            2 => "(GCCCGGGC)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ]
        ];

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->reduceEnzymesArray($aEnzymes, $iMinimum, $iRetype, $bDefinedSq, $sWre);
        $this->assertEquals($aExpected, $testFunction);
    }
    
    public function testRestrictionDigest()
    {
        $aEnzymes = $this->aType2;
        $sSequence = "ACGTACGTACGTTAGCTAGCTAGCTAGC";

        $aExpected = [
          "AfaI" => [
            "cuts" => [
              4 => "",
              8 => "",
            ],
          ],
          "AluI" => [
            "cuts" => [
              15 => "",
              19 => "",
              23 => "",
            ],
          ],
          "AsuNHI" => [
            "cuts" => [
              15 => "",
              19 => "",
              23 => "",
            ],
          ],
          "BfaI" => [
            "cuts" => [
              16 => "",
              20 => "",
              24 => "",
            ],
          ],
          "BmtI" => [
            "cuts" => [
              19 => "",
              23 => "",
              27 => "",
            ],
          ],
          "BsaAI" => [
            "cuts" => [
              6 => ""
            ],
          ],
          "BsiWI" => [
            "cuts" => [
              2 => "",
              6 => "",
            ],
          ],
          "BstC8I" => [
            "cuts" => [
              17 => "",
              21 => "",
              25 => "",
            ],
          ],
          "BstSNI" => [
            "cuts" => [
              6 => ""
            ]
          ],
          "Csp6I" => [
            "cuts" => [
              3 => "",
              7 => "",
            ]
          ],
          "CviJI" => [
            "cuts" => [
              15 => "",
              19 => "",
              23 => "",
            ]
          ],
          "HpyCH4IV" => [
            "cuts" => [
              1 => "",
              5 => "",
              9 => "",
            ]
          ],
          "SetI" => [
            "cuts" => [
              4 => "",
              8 => "",
              12 => "",
              17 => "",
              21 => "",
              25 => "",
            ]
          ],
          "TaiI" => [
            "cuts" => [
              4 => "",
              8 => "",
              12 => "",
            ]
          ]
        ];

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->restrictionDigest($aEnzymes, $sSequence);
        $this->assertEquals($aExpected, $testFunction);
    }

    public function testExtractSequencesOneSeq()
    {
        $sSequence = "ACGTACGTACGTTAGCTAGCTAGCTAGC";

        $aExpected = [
          0 => [
            "seq" => "ACGTACGTACGTTAGCTAGCTAGCTAGC"
          ]
        ];

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->extractSequences($sSequence);
        $this->assertEquals($aExpected, $testFunction);
    }

    public function testExtractSequencesTwoSeqs()
    {
        $sSequence = "ACGTACGTACGTTAGCTAGCTAGCTAGC>GTACGTTAGCTGTACGTTAGCT";

        $aExpected = [
          0 => [
            "seq" => "ACGTACGTACGTTAGCTAGCTAGCTAGC",
            "name" => ""
          ],
          1 =>  [
            "seq" => "GTACGTTAGCTGTACGTTAGCT",
            "name" => ""
          ]
        ];

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->extractSequences($sSequence);
        $this->assertEquals($aExpected, $testFunction);
    }

    public function testEnzymesForMultiSeqWreNullShowdiffs()
    {
        $aSequence = [
          0 => [
            "seq" => "ACGTACGTACGTTAGCTAGCTAGCTAGC",
            "name" => "",
          ],
          1 => [
            "seq" => "GTACGTTAGCTGTACGTTAGCT",
            "name" => "",
          ]
        ];

        $aDigestion = [
          0 => [
            "AfaI" => [
              "cuts" => [
                4 => "",
                8 => ""
              ]
            ],
            "AluI" => [
              "cuts" => [
                15 => "",
                19 => "",
                23 => ""
              ]
            ],
            "BstSNI" => [
              "cuts" => [
                6 => ""
              ]
            ]
          ],
          1 => [
            "AfaI" => [
              "cuts" => [
                2 => "",
                13 => ""
              ]
            ],
            "AluI" => [
              "cuts" => [
                9 => "",
                20 => ""
              ]
            ]
          ]
        ];

        $aEnzymes = [
          "AatI" => [
            0 => "AatI,Eco147I,PceI,SseBI,StuI",
            1 => "AGG'CCT",
            2 => "(AGGCCT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Acc16I" => [
            0 => "Acc16I,AviII,FspI,NsbI",
            1 => "TGC'GCA",
            2 => "(TGCGCA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AccII" => [
            0 => "AccII,Bsh1236I,BspFNI,BstFNI,BstUI,MvnI",
            1 => "CG'CG",
            2 => "(CGCG)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "AcvI" => [
            0 => "AcvI,BbrPI,Eco72I,PmaCI,PmlI,PspCI",
            1 => "CAC'GTG",
            2 => "(CACGTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AfaI" => [
            0 => "AfaI,RsaI",
            1 => "GT'AC",
            2 => "(GTAC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "AfeI" => [
            0 => "AfeI,Aor51HI,Eco47III",
            1 => "AGC'GCT",
            2 => "(AGCGCT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "AluI" => [
            0 => "AluI,AluBI",
            1 => "AG'CT",
            2 => "(AGCT)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "AssI" => [
            0 => "AssI,BmcAI,ScaI,ZrmI",
            1 => "AGT'ACT",
            2 => "(AGTACT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BalI" => [
            0 => "BalI,MlsI,MluNI,MscI,Msp20I",
            1 => "TGG'CCA",
            2 => "(TGGCCA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BshFI" => [
            0 => "BshFI,BsnI,BspANI,BsuRI,HaeIII,PhoI",
            1 => "GG'CC",
            2 => "(GGCC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "Bsp68I" => [
            0 => "Bsp68I,BtuMI,NruI,RruI",
            1 => "TCG'CGA",
            2 => "(TCGCGA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BssNAI" => [
            0 => "BssNAI,Bst1107I,BstZ17I",
            1 => "GTA'TAC",
            2 => "(GTATAC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "BstSNI" => [
            0 => "BstSNI,Eco105I,SnaBI",
            1 => "TAC'GTA",
            2 => "(TACGTA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "DpnI" => [
            0 => "DpnI,MalI",
            1 => "GA'TC",
            2 => "(GATC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "DraI" => [
            0 => "DraI",
            1 => "TTT'AAA",
            2 => "(TTTAAA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Ecl136II" => [
            0 => "Ecl136II,Eco53kI,EcoICRI",
            1 => "GAG'CTC",
            2 => "(GAGCTC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "Eco32I" => [
            0 => "Eco32I,EcoRV",
            1 => "GAT'ATC",
            2 => "(GATATC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "EgeI" => [
            0 => "EgeI,EheI,SfoI",
            1 => "GGC'GCC",
            2 => "(GGCGCC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "GlaI" => [
            0 => "GlaI",
            1 => "GC'GC",
            2 => "(GCGC)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "HpaI" => [
            0 => "HpaI,KspAI",
            1 => "GTT'AAC",
            2 => "(GTTAAC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "HpyCH4V" => [
            0 => "HpyCH4V",
            1 => "TG'CA",
            2 => "(TGCA)",
            3 => 4,
            4 => 2,
            5 => 0,
            6 => 4,
          ],
          "MssI" => [
            0 => "MssI,PmeI",
            1 => "GTTT'AAAC",
            2 => "(GTTTAAAC)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "NaeI" => [
            0 => "NaeI,PdiI",
            1 => "GCC'GGC",
            2 => "(GCCGGC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "PsiI" => [
            0 => "AanI,PsiI",
            1 => "TTA'TAA",
            2 => "(TTATAA)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "PvuII" => [
            0 => "PvuII",
            1 => "CAG'CTG",
            2 => "(CAGCTG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "SmaI" => [
            0 => "SmaI",
            1 => "CCC'GGG",
            2 => "(CCCGGG)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "SmiI" => [
            0 => "SmiI,SwaI",
            1 => "ATTT'AAAT",
            2 => "(ATTTAAAT)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "SrfI" => [
            0 => "SrfI",
            1 => "GCCC'GGGC",
            2 => "(GCCCGGGC)",
            3 => 8,
            4 => 4,
            5 => 0,
            6 => 8,
          ],
          "SspI" => [
            0 => "SspI",
            1 => "AAT'ATT",
            2 => "(AATATT)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ],
          "ZraI" => [
            0 => "ZraI",
            1 => "GAC'GTC",
            2 => "(GACGTC)",
            3 => 6,
            4 => 3,
            5 => 0,
            6 => 6,
          ]
        ];

        $bIsOnlyDiff = true;
        $sWre = "";

        $aExpected = [
          0 => "AluI"
        ];

        $service = new RestrictionDigestManager($this->apiMock);
        $testFunction = $service->enzymesForMultiSeq($aSequence, $aDigestion, $aEnzymes, $bIsOnlyDiff, $sWre);
        $this->assertEquals($aExpected, $testFunction);
    }
/*
    public function testEnzymesForMultiSeqWreNotNullNoDiffs()
    {
        $aSequence = [
          0 => [
            "seq" => "ACGTACGTACGTTAGCTAGCTAGCTAGC",
            "name" => ""
          ],
          1 => [
            "seq" => "GTACGTTAGCTGTACGTTAGCT",
            "name" => ""
          ]
        ];

        $aDigestion = [
            [
            "AfaI" => [
              "cuts" => [
                4 => "",
                8 => ""
              ]
            ],
            "AluI" => [
              "cuts" => [
                15 => "",
                19 => "",
                23 => ""
              ]
            ],
            "AsuNHI" => [
              "cuts" => [
                15 => "",
                19 => "",
                23 => ""
              ]
            ],
            "BfaI" => [
              "cuts" => [
                16 => "",
                20 => "",
                24 => ""
              ]
            ],
            "BmtI" => [
              "cuts" => [
                19 => "",
                23 => "",
                27 => ""
              ]
            ],
            "BsiWI" => [
              "cuts" => [
                2 => "",
                6 => ""
              ]
            ],
            "BstSNI" => [
              "cuts" => [
                6 => ""
              ]
            ],
            "Csp6I" => [
              "cuts" => [
                3 => "",
                7 => ""
              ]
            ],
            "HpyCH4IV" => [
              "cuts" => [
                1 => "",
                5 => "",
                9 => ""
              ]
            ],
            "TaiI" => [
              "cuts" => [
                4 => "",
                8 => "",
                12 => ""
              ]
            ]
          ],
          1 => [
            "AfaI" => [
              "cuts" => [
                2 => "",
                13 => ""
              ]
            ],
            "AluI" => [
              "cuts" => [
                9 => "",
                20 => ""
              ]
            ],
            "Csp6I" => [
              "cuts" => [
                1 => "",
                12 => ""
              ]
            ],
            "HpyCH4IV" => [
              "cuts" => [
                3 => "",
                14 => ""
              ]
            ],
            "TaiI" => [
              "cuts" => [
                6 => "",
                17 => ""
              ]
            ]
          ]
        ];

        $aEnzymes = [
  "AatI" => [
    0 => "AatI,Eco147I,PceI,SseBI,StuI",
    1 => "AGG'CCT",
    2 => "(AGGCCT)",
    3 => 6,
    4 => 3,
    5 => 0,
    6 => 6,
  ],
  "AatII" => [
    0 => "AatII",
    1 => "G_ACGT'C",
    2 => "(GACGTC)",
    3 => 6,
    4 => 5,
    5 => -4,
    6 => 6,
  ],
  "AbsI" => [
    0 => "AbsI",
    1 => "CC'TCGA_GG",
    2 => "(CCTCGAGG)",
    3 => 8,
    4 => 6,
    5 => -4,
    6 => 8,
  ],
  "Acc16I" => [
    0 => "Acc16I,AviII,FspI,NsbI",
    1 => "TGC'GCA",
    2 => "(TGCGCA)",
    3 => 6,
    4 => 3,
    5 => 0,
    6 => 6,
  ],
  "Acc65I" => [
    0 => "Acc65I,Asp718I",
    1 => "G'GTAC_C",
    2 => "(GGTACC)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "AccII" => [
    0 => "AccII,Bsh1236I,BspFNI,BstFNI,BstUI,MvnI",
    1 => "CG'CG",
    2 => "(CGCG)",
    3 => 4,
    4 => 2,
    5 => 0,
    6 => 4,
  ],
  "AccIII" => [
    0 => "AccIII,Aor13HI,BlfI,BseAI,Bsp13I,BspEI,Kpn2I,MroI",
    1 => "T'CCGG_A",
    2 => "(TCCGGA)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "AclI" => [
    0 => "AclI,Psp1406I",
    1 => "AA'CG_TT",
    2 => "(AACGTT)",
    3 => 6,
    4 => 2,
    5 => 2,
    6 => 6,
  ],
  "AcvI" => [
    0 => "AcvI,BbrPI,Eco72I,PmaCI,PmlI,PspCI",
    1 => "CAC'GTG",
    2 => "(CACGTG)",
    3 => 6,
    4 => 3,
    5 => 0,
    6 => 6,
  ],
  "AfaI" => [
    0 => "AfaI,RsaI",
    1 => "GT'AC",
    2 => "(GTAC)",
    3 => 4,
    4 => 2,
    5 => 0,
    6 => 4,
  ],
  "AfeI" => [
    0 => "AfeI,Aor51HI,Eco47III",
    1 => "AGC'GCT",
    2 => "(AGCGCT)",
    3 => 6,
    4 => 3,
    5 => 0,
    6 => 6,
  ],
  "AflII" => [
    0 => "AflII,BfrI,BspTI,Bst98I,BstAFI,MspCI,Vha464I",
    1 => "C'TTAA_G",
    2 => "(CTTAAG)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "AgeI" => [
    0 => "AgeI,AsiGI,BshTI,CspAI,PinAI",
    1 => "A'CCGG_T",
    2 => "(ACCGGT)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "AhlI" => [
    0 => "AhlI,BcuI,SpeI",
    1 => "A'CTAG_T",
    2 => "(ACTAGT)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "AluI" => [
    0 => "AluI,AluBI",
    1 => "AG'CT",
    2 => "(AGCT)",
    3 => 4,
    4 => 2,
    5 => 0,
    6 => 4,
  ],
  "Alw44I" => [
    0 => "Alw44I,ApaLI,VneI",
    1 => "G'TGCA_C",
    2 => "(GTGCAC)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "ApaI" => [
    0 => "ApaI",
    1 => "G_GGCC'C",
    2 => "(GGGCCC)",
    3 => 6,
    4 => 5,
    5 => -4,
    6 => 6,
  ],
  "AscI" => [
    0 => "AscI,PalAI,SgsI",
    1 => "GG'CGCG_CC",
    2 => "(GGCGCGCC)",
    3 => 8,
    4 => 2,
    5 => 4,
    6 => 8,
  ],
  "AseI" => [
    0 => "AseI,PshBI,VspI",
    1 => "AT'TA_AT",
    2 => "(ATTAAT)",
    3 => 6,
    4 => 2,
    5 => 2,
    6 => 6,
  ],
  "AsiSI" => [
    0 => "AsiSI,RgaI,SfaAI,SgfI",
    1 => "GCG_AT'CGC",
    2 => "(GCGATCGC)",
    3 => 8,
    4 => 5,
    5 => -2,
    6 => 8,
  ],
  "AspA2I" => [
    0 => "AspA2I,AvrII,BlnI,XmaJI",
    1 => "C'CTAG_G",
    2 => "(CCTAGG)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "AspLEI" => [
    0 => "AspLEI,BstHHI,CfoI,HhaI",
    1 => "G_CG'C",
    2 => "(GCGC)",
    3 => 4,
    4 => 3,
    5 => -2,
    6 => 4,
  ],
  "AssI" => [
    0 => "AssI,BmcAI,ScaI,ZrmI",
    1 => "AGT'ACT",
    2 => "(AGTACT)",
    3 => 6,
    4 => 3,
    5 => 0,
    6 => 6,
  ],
  "AsuII" => [
    0 => "AsuII,Bpu14I,Bsp119I,BspT104I,BstBI,Csp45I,NspV,SfuI",
    1 => "TT'CG_AA",
    2 => "(TTCGAA)",
    3 => 6,
    4 => 2,
    5 => 2,
    6 => 6,
  ],
  "AsuNHI" => [
    0 => "AsuNHI,BspOI,NheI",
    1 => "G'CTAG_C",
    2 => "(GCTAGC)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "BalI" => [
    0 => "BalI,MlsI,MluNI,MscI,Msp20I",
    1 => "TGG'CCA",
    2 => "(TGGCCA)",
    3 => 6,
    4 => 3,
    5 => 0,
    6 => 6,
  ],
  "BamHI" => [
    0 => "BamHI",
    1 => "G'GATC_C",
    2 => "(GGATCC)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "BanIII" => [
    0 => "BanIII,Bsa29I,BseCI,BshVI,BspDI,BspXI,Bsu15I,BsuTUI,ClaI",
    1 => "AT'CG_AT",
    2 => "(ATCGAT)",
    3 => 6,
    4 => 2,
    5 => 2,
    6 => 6,
  ],
  "BauI" => [
    0 => "BauI",
    1 => "C'ACGA_G",
    2 => "(CACGAG)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "BbeI" => [
    0 => "BbeI,PluTI",
    1 => "G_GCGC'C",
    2 => "(GGCGCC)",
    3 => 6,
    4 => 5,
    5 => -4,
    6 => 6,
  ],
  "BbuI" => [
    0 => "BbuI,PaeI,SphI",
    1 => "G_CATG'C",
    2 => "(GCATGC)",
    3 => 6,
    4 => 5,
    5 => -4,
    6 => 6,
  ],
  "BclI" => [
    0 => "BclI,FbaI,Ksp22I",
    1 => "T'GATC_A",
    2 => "(TGATCA)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "BfaI" => [
    0 => "BfaI,FspBI,MaeI,XspI",
    1 => "C'TA_G",
    2 => "(CTAG)",
    3 => 4,
    4 => 1,
    5 => 2,
    6 => 4,
  ],
  "BfuCI" => [
    0 => "BfuCI,Bsp143I,BssMI,BstMBI,DpnII,Kzo9I,MboI,NdeII,Sau3AI",
    1 => "'GATC_",
    2 => "(GATC)",
    3 => 4,
    4 => 0,
    5 => 4,
    6 => 4,
  ],
  "BglII" => [
    0 => "BglII",
    1 => "A'GATC_T",
    2 => "(AGATCT)",
    3 => 6,
    4 => 1,
    5 => 4,
    6 => 6,
  ],
  "BmtI" => [
    0 => "BmtI",
    1 => "G_CTAG'C",
    2 => "(GCTAGC)",
    3 => 6,
    4 => 5,
    5 => -4,
    6 => 6,
  ],
  "BpvUI" => [
    0 => "BpvUI,MvrI,PvuI,Ple19I"
    1 => "CG_AT'CG"
    2 => "(CGATCG)"
    3 => 6
    4 => 4
    5 => -2
    6 => 6
  ]
  "BsePI" => [
    0 => "BsePI,BssHII,PauI,PteI"
    1 => "G'CGCG_C"
    2 => "(GCGCGC)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "BseX3I" => [
    0 => "BseX3I,BstZI,EagI,EclXI,Eco52I"
    1 => "C'GGCC_G"
    2 => "(CGGCCG)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "BshFI" => [
    0 => "BshFI,BsnI,BspANI,BsuRI,HaeIII,PhoI"
    1 => "GG'CC"
    2 => "(GGCC)"
    3 => 4
    4 => 2
    5 => 0
    6 => 4
  ]
  "BsiSI" => [
    0 => "BsiSI,HapII,HpaII,MspI"
    1 => "C'CG_G"
    2 => "(CCGG)"
    3 => 4
    4 => 1
    5 => 2
    6 => 4
  ]
  "BsiWI" => [
    0 => "BsiWI,Pfl23II,PspLI"
    1 => "C'GTAC_G"
    2 => "(CGTACG)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "Bsp120I" => [
    0 => "Bsp120I,PspOMI"
    1 => "G'GGCC_C"
    2 => "(GGGCCC)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "Bsp1407I" => [
    0 => "Bsp1407I,BsrGI,BstAUI,SspBI"
    1 => "T'GTAC_A"
    2 => "(TGTACA)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "Bsp19I" => [
    0 => "Bsp19I,NcoI"
    1 => "C'CATG_G"
    2 => "(CCATGG)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "Bsp68I" => [
    0 => "Bsp68I,BtuMI,NruI,RruI"
    1 => "TCG'CGA"
    2 => "(TCGCGA)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "BspHI" => [
    0 => "BspHI,CciI,PagI,RcaI"
    1 => "T'CATG_A"
    2 => "(TCATGA)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "BspLU11I" => [
    0 => "BspLU11I,PciI,PscI"
    1 => "A'CATG_T"
    2 => "(ACATGT)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "BspMAI" => [
    0 => "BspMAI,PstI"
    1 => "C_TGCA'G"
    2 => "(CTGCAG)"
    3 => 6
    4 => 5
    5 => -4
    6 => 6
  ]
  "BssNAI" => [
    0 => "BssNAI,Bst1107I,BstZ17I"
    1 => "GTA'TAC"
    2 => "(GTATAC)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "BstKTI" => [
    0 => "BstKTI"
    1 => "G_AT'C"
    2 => "(GATC)"
    3 => 4
    4 => 3
    5 => 2
    6 => 4
  ]
  "BstSNI" => [
    0 => "BstSNI,Eco105I,SnaBI"
    1 => "TAC'GTA"
    2 => "(TACGTA)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "CciNI" => [
    0 => "CciNI,NotI"
    1 => "GC'GGCC_GC"
    2 => "(GCGGCCGC)"
    3 => 8
    4 => 2
    5 => 4
    6 => 8
  ]
  "Cfr42I" => [
    0 => "Cfr42I,KspI,SacII,Sfr303I,SgrBI,SstII"
    1 => "CC_GC'GG"
    2 => "(CCGCGG)"
    3 => 6
    4 => 4
    5 => -2
    6 => 6
  ]
  "Cfr9I" => [
    0 => "Cfr9I,TspMI,XmaI,XmaCI"
    1 => "C'CCGG_G"
    2 => "(CCCGGG)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "Csp6I" => [
    0 => "Csp6I,CviQI,RsaNI"
    1 => "G'TA_C"
    2 => "(GTAC)"
    3 => 4
    4 => 1
    5 => 2
    6 => 4
  ]
  "CviAII" => [
    0 => "CviAII,FaeI,Hin1II,Hsp92II,NlaIII"
    1 => "_CATG'"
    2 => "(CATG)"
    3 => 4
    4 => 4
    5 => -4
    6 => 4
  ]
  "DinI" => [
    0 => "DinI,Mly113I,NarI,SspDI"
    1 => "GG'CG_CC"
    2 => "(GGCGCC)"
    3 => 6
    4 => 2
    5 => 2
    6 => 6
  ]
  "DpnI" => [
    0 => "DpnI,MalI"
    1 => "GA'TC"
    2 => "(GATC)"
    3 => 4
    4 => 2
    5 => 0
    6 => 4
  ]
  "DraI" => [
    0 => "DraI"
    1 => "TTT'AAA"
    2 => "(TTTAAA)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "Ecl136II" => [
    0 => "Ecl136II,Eco53kI,EcoICRI"
    1 => "GAG'CTC"
    2 => "(GAGCTC)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "Eco32I" => [
    0 => "Eco32I,EcoRV"
    1 => "GAT'ATC"
    2 => "(GATATC)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "EcoRI" => [
    0 => "EcoRI"
    1 => "G'AATT_C"
    2 => "(GAATTC)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "EcoT22I" => [
    0 => "EcoT22I,Mph1103I,NsiI,Zsp2I"
    1 => "A_TGCA'T"
    2 => "(ATGCAT)"
    3 => 6
    4 => 5
    5 => -4
    6 => 6
  ]
  "EgeI" => [
    0 => "EgeI,EheI,SfoI"
    1 => "GGC'GCC"
    2 => "(GGCGCC)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "FatI" => [
    0 => "FatI"
    1 => "'CATG_"
    2 => "(CATG)"
    3 => 4
    4 => 0
    5 => 4
    6 => 4
  ]
  "FauNDI" => [
    0 => "FauNDI,NdeI"
    1 => "CA'TA_TG"
    2 => "(CATATG)"
    3 => 6
    4 => 2
    5 => 2
    6 => 6
  ]
  "FseI" => [
    0 => "FseI,RigI"
    1 => "GG_CCGG'CC"
    2 => "(GGCCGGCC)"
    3 => 8
    4 => 6
    5 => -4
    6 => 8
  ]
  "GlaI" => [
    0 => "GlaI"
    1 => "GC'GC"
    2 => "(GCGC)"
    3 => 4
    4 => 2
    5 => 0
    6 => 4
  ]
  "Hin6I" => [
    0 => "Hin6I,HinP1I,HspAI"
    1 => "G'CG_C"
    2 => "(GCGC)"
    3 => 4
    4 => 1
    5 => 2
    6 => 4
  ]
  "HindIII" => [
    0 => "HindIII"
    1 => "A'AGCT_T"
    2 => "(AAGCTT)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "HpaI" => [
    0 => "HpaI,KspAI"
    1 => "GTT'AAC"
    2 => "(GTTAAC)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "HpyCH4IV" => [
    0 => "HpyCH4IV,HpySE526I,MaeII"
    1 => "A'CG_T"
    2 => "(ACGT)"
    3 => 4
    4 => 1
    5 => 2
    6 => 4
  ]
  "HpyCH4V" => [
    0 => "HpyCH4V"
    1 => "TG'CA"
    2 => "(TGCA)"
    3 => 4
    4 => 2
    5 => 0
    6 => 4
  ]
  "KasI" => [
    0 => "KasI"
    1 => "G'GCGC_C"
    2 => "(GGCGCC)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "KpnI" => [
    0 => "KpnI"
    1 => "G_GTAC'C"
    2 => "(GGTACC)"
    3 => 6
    4 => 5
    5 => -4
    6 => 6
  ]
  "KroI" => [
    0 => "KroI,MroNI,NgoMIV"
    1 => "G'CCGG_C"
    2 => "(GCCGGC)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "MauBI" => [
    0 => "MauBI"
    1 => "CG'CGCG_CG"
    2 => "(CGCGCGCG)"
    3 => 8
    4 => 2
    5 => 4
    6 => 8
  ]
  "MfeI" => [
    0 => "MfeI,MunI"
    1 => "C'AATT_G"
    2 => "(CAATTG)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "MluCI" => [
    0 => "MluCI,Sse9I,TasI,Tsp509I,TspEI"
    1 => "'AATT_"
    2 => "(AATT)"
    3 => 4
    4 => 0
    5 => 4
    6 => 4
  ]
  "MluI" => [
    0 => "MluI"
    1 => "A'CGCG_T"
    2 => "(ACGCGT)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "MreI" => [
    0 => "MreI"
    1 => "CG'CCGG_CG"
    2 => "(CGCCGGCG)"
    3 => 8
    4 => 2
    5 => 4
    6 => 8
  ]
  "MseI" => [
    0 => "MseI,SaqAI,Tru1I,Tru9I"
    1 => "T'TA_A"
    2 => "(TTAA)"
    3 => 4
    4 => 1
    5 => 2
    6 => 4
  ]
  "MssI" => [
    0 => "MssI,PmeI"
    1 => "GTTT'AAAC"
    2 => "(GTTTAAAC)"
    3 => 8
    4 => 4
    5 => 0
    6 => 8
  ]
  "NaeI" => [
    0 => "NaeI,PdiI"
    1 => "GCC'GGC"
    2 => "(GCCGGC)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "PacI" => [
    0 => "PacI"
    1 => "TTA_AT'TAA"
    2 => "(TTAATTAA)"
    3 => 8
    4 => 5
    5 => -2
    6 => 8
  ]
  "PaeR7I" => [
    0 => "PaeR7I,Sfr274I,SlaI,StrI,TliI,XhoI"
    1 => "C'TCGA_G"
    2 => "(CTCGAG)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "PsiI" => [
    0 => "AanI,PsiI"
    1 => "TTA'TAA"
    2 => "(TTATAA)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "Psp124BI" => [
    0 => "Psp124BI,SacI,SstI"
    1 => "G_AGCT'C"
    2 => "(GAGCTC)"
    3 => 6
    4 => 5
    5 => -44
    6 => 6
  ]
  "PvuII" => [
    0 => "PvuII"
    1 => "CAG'CTG"
    2 => "(CAGCTG)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "SalI" => [
    0 => "SalI"
    1 => "G'TCGA_C"
    2 => "(GTCGAC)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "SbfI" => [
    0 => "SbfI,SdaI,Sse8387I"
    1 => "CC_TGCA'GG"
    2 => "(CCTGCAGG)"
    3 => 8
    4 => 6
    5 => -4
    6 => 8
  ]
  "SgrDI" => [
    0 => "SgrDI"
    1 => "CG'TCGA_CG"
    2 => "(CGTCGACG)"
    3 => 8
    4 => 2
    5 => 4
    6 => 8
  ]
  "SmaI" => [
    0 => "SmaI"
    1 => "CCC'GGG"
    2 => "(CCCGGG)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "SmiI" => [
    0 => "SmiI,SwaI"
    1 => "ATTT'AAAT"
    2 => "(ATTTAAAT)"
    3 => 8
    4 => 4
    5 => 0
    6 => 8
  ]
  "SrfI" => [
    0 => "SrfI"
    1 => "GCCC'GGGC"
    2 => "(GCCCGGGC)"
    3 => 8
    4 => 4
    5 => 0
    6 => 8
  ]
  "SspI" => [
    0 => "SspI"
    1 => "AAT'ATT"
    2 => "(AATATT)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
  "TaiI" => [
    0 => "TaiI"
    1 => "_ACGT'"
    2 => "(ACGT)"
    3 => 4
    4 => 4
    5 => -4
    6 => 4
  ]
  "TaqI" => [
    0 => "TaqI"
    1 => "T'CG_A"
    2 => "(TCGA)"
    3 => 4
    4 => 1
    5 => 2
    6 => 4
  ]
  "XbaI" => [
    0 => "XbaI"
    1 => "T'CTAG_A"
    2 => "(TCTAGA)"
    3 => 6
    4 => 1
    5 => 4
    6 => 6
  ]
  "ZraI" => [
    0 => "ZraI"
    1 => "GAC'GTC"
    2 => "(GACGTC)"
    3 => 6
    4 => 3
    5 => 0
    6 => 6
  ]
];
        $bIsOnlyDiff = false;
        $sWre = "AarI";

        $sExpected = [
          0 => "AfaI",
          1 => "AfaI",
          2 => "AluI",
          3 => "AluI",
          4 => "AsuNHI",
          5 => "BfaI",
          6 => "BmtI",
          7 => "BsiWI",
          8 => "BstSNI",
          9 => "Csp6I",
          10 => "Csp6I",
          11 => "HpyCH4IV",
          12 => "HpyCH4IV",
          13 => "TaiI",
          14 => "TaiI",
        ];
    }*/
}