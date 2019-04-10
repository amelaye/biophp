<?php
// RestEn.inc - contains definition of RestEn (restriction endonucleases) class.

/*
parse_args() parses arguments that are passed to a function as a single string in order 
to mimic BioPerl's syntax: new SomeClass(argname1 => value1, argname2 => value2, ... ).
I am actually thinking of dropping this in favor of the more cumbersome format native
to PHP: new SomeClass( array(argname1 => value1, argname2 => value2, ... ) ). 
*/
function parse_args($args)
	{
	$arguments = array();
	preg_match_all("/\'[a-zA-Z0-9]*\'=>\'[^\']+\'/", $args, $match);
	foreach($match[0] as $assign_string)
		{
		$temp = preg_split("/=>/", $assign_string);
		$argname = $temp[0];
		$argvalue = $temp[1];
		$argname = eregi_replace("'", "", $argname);
		$argvalue = eregi_replace("'", "", $argvalue);
		$arguments[$argname] = $argvalue;
		}
	return $arguments;
	}

$RestEn_DB = array(
	'AasI'      => array('GACNNNNNNGTC', 7),
	'AatI'      => array('AGGCCT', 3),
	'AatII'     => array('GACGTC', 5),
	"AauI"      => array("TGTACA", 1),
	'AccI'      => array('GTMKAC', 2),
	"AccII"     => array("CGCG", 2),
	"AccIII"    => array("TCCGGA",1),
	'Acc16I'    => array('TGCGCA', 3),
	"Acc65I"    => array("GGTACC", 1),
	'Acc113I'   => array('AGTACT', 3),
	"AccB1I"    => array("GGYRCC", 1),
	'AccB7I'    => array('CCANNNNNTGG', 7),
	"AclI"      => array("AACGTT", 2),
	"AcsI"      => array("RAATTY", 1),
	'AcvI'      => array('CACGTG', 3),
	'AcyI'      => array('GRCGYC', 2),
	'AdeI'      => array('CACNNNGTG', 6),
	'AfaI'      => array('GTAC', 2),
	'AfeI'      => array('AGCGCT', 3),
	'AflI'      => array('GGWCC', 1),
	'AflII'     => array('CTTAAG', 1),
	'AflIII'    => array('ACRYGT', 1),
	'AgeI'      => array('ACCGGT', 1),
	'AhaIII'    => array('TTTAAA', 3),
	'AhdI'      => array('GACNNNNNGTC', 6),
	'AhlI'      => array('ACTAGT', 1),
	'AleI'      => array('CACNNNNGTG', 5),
	'AluI'      => array('AGCT', 2),
	'Alw21I'    => array('GWGCWC', 5),
	'Alw44I'    => array('GTGCAC', 1),
	'AlwNI'     => array('CAGNNNCTG', 6),
	'Ama87I'    => array('CYCGRG', 1),
	'AocI'      => array('CCTNAGG', 2),
	'Aor51HI'   => array('AGCGCT', 3),
	'ApaI'      => array('GGGCCC', 5),
	'ApaBI'     => array('GCANNNNNTGC', 8),
	'ApaLI'     => array('GTGCAC', 1),
	'ApoI'      => array('RAATTY', 1),
	'AscI'      => array('GGCGCGCC', 2),
	'AseI'      => array('ATTAAT', 2),
	'AsiAI'     => array('ACCGGT', 1),
	'AsiSI'     => array('GCGATCGC', 5),
	'AsnI'      => array('ATTAAT', 2),
	'AspI'      => array('GACNNNGTC', 4),
	'Asp700I'   => array('GAANNNNTTC', 5),
	'Asp718I'   => array('GGTACC', 1),
	'AspEI'     => array('GACNNNNNGTC', 6),
	'AspHI'     => array('GWGCWC', 5),
	'AspLEI'    => array('GCGC', 3),
	'AspS9I'    => array('GGNCC', 1),
	'AsuI'      => array('GGNCC', 1),
	'AsuII'     => array('TTCGAA', 2),
	'AsuC2I'    => array('CCSGG', 2),
	'AsuNHI'    => array('GCTAGC', 1),
	'AvaI'      => array('CYCGRG', 1),
	'AvaII'     => array('GGWCC', 1),
	'AviII'     => array('TGCGCA', 3),
	'AvrII'     => array('CCTAGG', 1),
	'AxyI'      => array('CCTNAGG', 2),
	'BalI'      => array('TGGCCA', 3),
	'BamHI'     => array('GGATCC', 1),
	'BanI'      => array('GGYRCC', 1),
	'BanII'     => array('GRGCYC', 5),
	'BanIII'    => array('ATCGAT', 2),
	'BbeI'      => array('GGCGCC', 5),
	'BbrPI'     => array('CACGTG', 3),
	'BbuI'      => array('GCATGC', 5),
	'Bbv12I'    => array('GWGCWC', 5),
	'BclI'      => array('TGATCA', 1),
	'BcnI'      => array('CCSGG', 2),
	'BcoI'      => array('CYCGRG', 1),
	'BcuI'      => array('ACTAGT', 1),
	'BetI'      => array('WCCGGW', 1),
	'BfaI'      => array('CTAG', 1),
	'BfmI'      => array('CTRYAG', 1),
	'BfrI'      => array('CTTAAG', 1),
	'BfrBI'     => array('ATGCAT', 3),
	'BfuCI'     => array('GATC', 0),
	'BglI'      => array('GCCNNNNNGGC', 7),
	'BglII'     => array('AGATCT', 1),
	'BlnI'      => array('CCTAGG', 1),
	'BloHII'    => array('CTGCAG', 5),
	'BlpI'      => array('GCTNAGC', 2),
	'Bme18I'    => array('GGWCC', 1),
	'Bme1390I'  => array('CCNGG', 2),
	'Bme1580I'  => array('GKGCMC', 5),
	'BmtI'      => array('GCTAGC', 5),
	'BmyI'      => array('GDGCHC', 5),
	'BoxI'      => array('GACNNNNGTC', 5),
	'Bpu14I'    => array('TTCGAA', 2),
	'Bpu1102I'  => array('GCTNAGC', 2),
	'Bsa29I'    => array('ATCGAT', 2),
	'BsaAI'     => array('YACGTR', 3),
	'BsaBI'     => array('GATNNNNATC', 5),
	'BsaHI'     => array('GRCGYC', 2),
	'BsaJI'     => array('CCNNGG', 1),
	'BsaOI'     => array('CGRYCG', 4),
	'BsaWI'     => array('WCCGGW', 1),
	'BscI'      => array('ATCGAT', 2),
	'Bsc4I'     => array('CCNNNNNNNGG', 7),
	'BscBI'     => array('GGNNCC', 3),
	'BscFI'     => array('GATC', 0),
	'Bse8I'     => array('GATNNNNATC', 5),
	'Bse21I'    => array('CCTNAGG', 2),
	'Bse118I'   => array('RCCGGY', 1),
	'BseAI'     => array('TCCGGA', 1),
	'BseBI'     => array('CCWGG', 2),
	'BseCI'     => array('ATCGAT', 2),
	'BseDI'     => array('CCNNGG', 1),
	'BseJI'     => array('GATNNNNATC', 5),
	'BseLI'     => array('CCNNNNNNNGG', 7),
	'BsePI'     => array('GCGCGC', 1),
	'BseSI'     => array('GKGCMC', 5),
	'BseX3I'    => array('CGGCCG', 1),
	'BshI'      => array('GGCC', 2),
	'Bsh1236I'  => array('CGCG', 2),
	'Bsh1285I'  => array('CGRYCG', 4),
	'BshFI'     => array('GGCC', 2),
	'BshNI'     => array('GGYRCC', 1),
	'BshTI'     => array('ACCGGT', 1),
	'BsiBI'     => array('GATNNNNATC', 5),
	'BsiCI'     => array('TTCGAA', 2),
	'BsiEI'     => array('CGRYCG', 4),
	'BsiHKAI'   => array('GWGCWC', 5),
	'BsiHKCI'   => array('CYCGRG', 1),
	'BsiLI'     => array('CCWGG', 2),
	'BsiMI'     => array('TCCGGA', 1),
	'BsiQI'     => array('TGATCA', 1),
	'BsiSI'     => array('CCGG', 1),
	'BsiWI'     => array('CGTACG', 1),
	'BsiXI'     => array('ATCGAT', 2),
	'BsiYI'     => array('CCNNNNNNNGG', 7),
	'BsiZI'     => array('GGNCC', 1),
	'BslI'      => array('CCNNNNNNNGG', 7),
	'BsoBI'     => array('CYCGRG', 1),
	'Bsp13I'    => array('TCCGGA', 1),
	'Bsp19I'    => array('CCATGG', 1),
	'Bsp68I'    => array('TCGCGA', 3),
	'Bsp106I'   => array('ATCGAT', 2),
	'Bsp119I'   => array('TTCGAA', 2),
	'Bsp120I'   => array('GGGCCC', 1),
	'Bsp143I'   => array('GATC', 0),
	'Bsp143II'  => array('RGCGCY', 5),
	'Bsp1286I'  => array('GDGCHC', 5),
	'Bsp1407I'  => array('TGTACA', 1),
	'Bsp1720I'  => array('GCTNAGC', 2),
	'BspA2I'    => array('CCTAGG', 1),
	'BspCI'     => array('CGATCG', 4),
	'BspDI'     => array('ATCGAT', 2),
	'BspEI'     => array('TCCGGA', 1),
	'BspHI'     => array('TCATGA', 1),
	'BspLI'     => array('GGNNCC', 3),
	'BspLU11I'  => array('ACATGT', 1),
	'BspMII'    => array('TCCGGA', 1),
	'BspTI'     => array('CTTAAG', 1),
	'BspT104I'  => array('TTCGAA', 2),
	'BspT107I'  => array('GGYRCC', 1),
	'BspXI'     => array('ATCGAT', 2),
	'BsrBRI'    => array('GATNNNNATC', 5),
	'BsrFI'     => array('RCCGGY', 1),
	'BsrGI'     => array('TGTACA', 1),
	'BssAI'     => array('RCCGGY', 1),
	'BssECI'    => array('CCNNGG', 1),
	'BssHI'     => array('CTCGAG', 1),
	'BssHII'    => array('GCGCGC', 1),
	'BssKI'     => array('CCNGG', 0),
	'BssNAI'    => array('GTATAC', 3),
	'BssT1I'    => array('CCWWGG', 1),
	'Bst98I'    => array('CTTAAG', 1),
	'Bst1107I'  => array('GTATAC', 3),
	'BstACI'    => array('GRCGYC', 2),
	'BstAPI'    => array('GCANNNNNTGC', 7),
	'BstBI'     => array('TTCGAA', 2),
	'BstBAI'    => array('YACGTR', 3),
	'Bst4CI'    => array('ACNGT', 3),
	'BstC8I'    => array('GCNNGC', 3),
	'BstDEI'    => array('CTNAG', 1),
	'BstDSI'    => array('CCRYGG', 1),
	'BstEII'    => array('GGTNACC', 1),
	'BstENI'    => array('CCTNNNNNAGG', 5),
	'BstENII'   => array('GATC', 0),
	'BstFNI'    => array('CGCG', 2),
	'BstH2I'    => array('RGCGCY', 5),
	'BstHHI'    => array('GCGC', 3),
	'BstHPI'    => array('GTTAAC', 3),
	'BstKTI'    => array('GATC', 3),
	'BstMAI'    => array('CTGCAG', 5),
	'BstMCI'    => array('CGRYCG', 4),
	'BstMWI'    => array('GCNNNNNNNGC', 7),
	'BstNI'     => array('CCWGG', 2),
	'BstNSI'    => array('RCATGY', 5),
	'BstOI'     => array('CCWGG', 2),
	'BstPI'     => array('GGTNACC', 1),
	'BstPAI'    => array('GACNNNNGTC', 5),
	'BstSCI'    => array('CCNGG', 0),
	'BstSFI'    => array('CTRYAG', 1),
	'BstSNI'    => array('TACGTA', 3),
	'BstUI'     => array('CGCG', 2),
	'Bst2UI'    => array('CCWGG', 2),
	'BstXI'     => array('CCANNNNNNTGG', 8),
	'BstX2I'    => array('RGATCY', 1),
	'BstYI'     => array('RGATCY', 1),
	'BstZI'     => array('CGGCCG', 1),
	'BstZ17I'   => array('GTATAC', 3),
	'Bsu15I'    => array('ATCGAT', 2),
	'Bsu36I'    => array('CCTNAGG', 2),
	'BsuRI'     => array('GGCC', 2),
	'BsuTUI'    => array('ATCGAT', 2),
	'BtgI'      => array('CCRYGG', 1),
	'BthCI'     => array('GCNGC', 4),
	'Cac8I'     => array('GCNNGC', 3),
	'CaiI'      => array('CAGNNNCTG', 6),
	'CauII'     => array('CCSGG', 2),
	'CciNI'     => array('GCGGCCGC', 2),
	'CelII'     => array('GCTNAGC', 2),
	'CfoI'      => array('GCGC', 3),
	'CfrI'      => array('YGGCCR', 1),
	'Cfr9I'     => array('CCCGGG', 1),
	'Cfr10I'    => array('RCCGGY', 1),
	'Cfr13I'    => array('GGNCC', 1),
	'Cfr42I'    => array('CCGCGG', 4),
	'ChaI'      => array('GATC', 4),
	'ClaI'      => array('ATCGAT', 2),
	'CpoI'      => array('CGGWCCG', 2),
	'CspI'      => array('CGGWCCG', 2),
	'Csp6I'     => array('GTAC', 1),
	'Csp45I'    => array('TTCGAA', 2),
	'CspAI'     => array('ACCGGT', 1),
	'CviAII'    => array('CATG', 1),
	'CviJI'     => array('RGCY', 2),
	'CviRI'     => array('TGCA', 2),
	'CviTI'     => array('RGCY', 2),
	'CvnI'      => array('CCTNAGG', 2),
	'DdeI'      => array('CTNAG', 1),
	'DpnI'      => array('GATC', 2),
	'DpnII'     => array('GATC', 0),
	'DraI'      => array('TTTAAA', 3),
	'DraII'     => array('RGGNCCY', 2),
	'DraIII'    => array('CACNNNGTG', 6),
	'DrdI'      => array('GACNNNNNNGTC', 7),
	'DsaI'      => array('CCRYGG', 1),
	'DseDI'     => array('GACNNNNNNGTC', 7),
	'EaeI'      => array('YGGCCR', 1),
	'EagI'      => array('CGGCCG', 1),
	'Eam1105I'  => array('GACNNNNNGTC', 6),
	'Ecl136II'  => array('GAGCTC', 3),
	'EclHKI'    => array('GACNNNNNGTC', 6),
	'EclXI'     => array('CGGCCG', 1),
	'Eco24I'    => array('GRGCYC', 5),
	'Eco32I'    => array('GATATC', 3),
	'Eco47I'    => array('GGWCC', 1),
	'Eco47III'  => array('AGCGCT', 3),
	'Eco52I'    => array('CGGCCG', 1),
	'Eco72I'    => array('CACGTG', 3),
	'Eco81I'    => array('CCTNAGG', 2),
	'Eco88I'    => array('CYCGRG', 1),
	'Eco91I'    => array('GGTNACC', 1),
	'Eco105I'   => array('TACGTA', 3),
	'Eco130I'   => array('CCWWGG', 1),
	'Eco147I'   => array('AGGCCT', 3),
	'EcoHI'     => array('CCSGG', 0),
	'EcoICRI'   => array('GAGCTC', 3),
	'EcoNI'     => array('CCTNNNNNAGG', 5),
	'EcoO65I'   => array('GGTNACC', 1),
	'EcoO109I'  => array('RGGNCCY', 2),
	'EcoRI'     => array('GAATTC', 1),
	'EcoRII'    => array('CCWGG', 0),
	'EcoRV'     => array('GATATC', 3),
	'EcoT14I'   => array('CCWWGG', 1),
	'EcoT22I'   => array('ATGCAT', 5),
	'EcoT38I'   => array('GRGCYC', 5),
	'EgeI'      => array('GGCGCC', 3),
	'EheI'      => array('GGCGCC', 3),
	'ErhI'      => array('CCWWGG', 1),
	'EsaBC3I'   => array('TCGA', 2),
	'EspI'      => array('GCTNAGC', 2),
	'FatI'      => array('CATG', 0),
	'FauNDI'    => array('CATATG', 2),
	'FbaI'      => array('TGATCA', 1),
	'FblI'      => array('GTMKAC', 2),
	'FmuI'      => array('GGNCC', 4),
	'FnuDII'    => array('CGCG', 2),
	'Fnu4HI'    => array('GCNGC', 2),
	'FriOI'     => array('GRGCYC', 5),
	'FseI'      => array('GGCCGGCC', 6),
	'FspI'      => array('TGCGCA', 3),
	'FspAI'     => array('RTGCGCAY', 4),
	'Fsp4HI'    => array('GCNGC', 2),
	'FunI'      => array('AGCGCT', 3),
	'FunII'     => array('GAATTC', 1),
	'HaeI'      => array('WGGCCW', 3),
	'HaeII'     => array('RGCGCY', 5),
	'HaeIII'    => array('GGCC', 2),
	'HapII'     => array('CCGG', 1),
	'HgiAI'     => array('GWGCWC', 5),
	'HgiCI'     => array('GGYRCC', 1),
	'HgiJII'    => array('GRGCYC', 5),
	'HhaI'      => array('GCGC', 3),
	'Hin1I'     => array('GRCGYC', 2),
	'Hin6I'     => array('GCGC', 1),
	'HinP1I'    => array('GCGC', 1),
	'HincII'    => array('GTYRAC', 3),
	'HindII'    => array('GTYRAC', 3),
	'HindIII'   => array('AAGCTT', 1),
	'HinfI'     => array('GANTC', 1),
	'HpaI'      => array('GTTAAC', 3),
	'HpaII'     => array('CCGG', 1),
	'Hpy8I'     => array('GTNNAC', 3),
	'Hpy99I'    => array('CGWCG', 5),
	'Hpy178III' => array('TCNNGA', 2),
	'Hpy188I'   => array('TCNGA', 3),
	'Hpy188III' => array('TCNNGA', 2),
	'HpyCH4I'   => array('CATG', 3),
	'HpyCH4III' => array('ACNGT', 3),
	'HpyCH4IV'  => array('ACGT', 1),
	'HpyCH4V'   => array('TGCA', 2),
	'HpyF10VI'  => array('GCNNNNNNNGC', 8),
	'Hsp92I'    => array('GRCGYC', 2),
	'Hsp92II'   => array('CATG', 4),
	'HspAI'     => array('GCGC', 1),
	'ItaI'      => array('GCNGC', 2),
	'KasI'      => array('GGCGCC', 1),
	'KpnI'      => array('GGTACC', 5),
	'Kpn2I'     => array('TCCGGA', 1),
	'KspI'      => array('CCGCGG', 4),
	'Ksp22I'    => array('TGATCA', 1),
	'KspAI'     => array('GTTAAC', 3),
	'Kzo9I'     => array('GATC', 0),
	'LpnI'      => array('RGCGCY', 3),
	'LspI'      => array('TTCGAA', 2),
	'MabI'      => array('ACCWGGT', 1),
	'MaeI'      => array('CTAG', 1),
	'MaeII'     => array('ACGT', 1),
	'MaeIII'    => array('GTNAC', 0),
	'MamI'      => array('GATNNNNATC', 5),
	'MboI'      => array('GATC', 0),
	'McrI'      => array('CGRYCG', 4),
	'MfeI'      => array('CAATTG', 1),
	'MflI'      => array('RGATCY', 1),
	'MhlI'      => array('GDGCHC', 5),
	'MlsI'      => array('TGGCCA', 3),
	'MluI'      => array('ACGCGT', 1),
	'MluNI'     => array('TGGCCA', 3),
	'Mly113I'   => array('GGCGCC', 2),
	'Mph1103I'  => array('ATGCAT', 5),
	'MroI'      => array('TCCGGA', 1),
	'MroNI'     => array('GCCGGC', 1),
	'MroXI'     => array('GAANNNNTTC', 5),
	'MscI'      => array('TGGCCA', 3),
	'MseI'      => array('TTAA', 1),
	'MslI'      => array('CAYNNNNRTG', 5),
	'MspI'      => array('CCGG', 1),
	'Msp20I'    => array('TGGCCA', 3),
	'MspA1I'    => array('CMGCKG', 3),
	'MspCI'     => array('CTTAAG', 1),
	'MspR9I'    => array('CCNGG', 2),
	'MssI'      => array('GTTTAAAC', 4),
	'MstI'      => array('TGCGCA', 3),
	'MunI'      => array('CAATTG', 1),
	'MvaI'      => array('CCWGG', 2),
	'MvnI'      => array('CGCG', 2),
	'MwoI'      => array('GCNNNNNNNGC', 7),
	'NaeI'      => array('GCCGGC', 3),
	'NarI'      => array('GGCGCC', 2),
	'NciI'      => array('CCSGG', 2),
	'NcoI'      => array('CCATGG', 1),
	'NdeI'      => array('CATATG', 2),
	'NdeII'     => array('GATC', 0),
	'NgoAIV'    => array('GCCGGC', 1),
	'NgoMIV'    => array('GCCGGC', 1),
	'NheI'      => array('GCTAGC', 1),
	'NlaIII'    => array('CATG', 4),
	'NlaIV'     => array('GGNNCC', 3),
	'Nli3877I'  => array('CYCGRG', 5),
	'NmuCI'     => array('GTSAC', 0),
	'NotI'      => array('GCGGCCGC', 2),
	'NruI'      => array('TCGCGA', 3),
	'NruGI'     => array('GACNNNNNGTC', 6),
	'NsbI'      => array('TGCGCA', 3),
	'NsiI'      => array('ATGCAT', 5),
	'NspI'      => array('RCATGY', 5),
	'NspIII'    => array('CYCGRG', 1),
	'NspV'      => array('TTCGAA', 2),
	'NspBII'    => array('CMGCKG', 3),
	'OliI'      => array('CACNNNNGTG', 5),
	'PacI'      => array('TTAATTAA', 5),
	'PaeI'      => array('GCATGC', 5),
	'PaeR7I'    => array('CTCGAG', 1),
	'PagI'      => array('TCATGA', 1),
	'PalI'      => array('GGCC', 2),
	'PauI'      => array('GCGCGC', 1),
	'PceI'      => array('AGGCCT', 3),
	'PciI'      => array('ACATGT', 1),
	'PdiI'      => array('GCCGGC', 3),
	'PdmI'      => array('GAANNNNTTC', 5),
	'Pfl23II'   => array('CGTACG', 1),
	'PflBI'     => array('CCANNNNNTGG', 7),
	'PflFI'     => array('GACNNNGTC', 4),
	'PflMI'     => array('CCANNNNNTGG', 7),
	'PfoI'      => array('TCCNGGA', 1),
	'PinAI'     => array('ACCGGT', 1),
	'Ple19I'    => array('CGATCG', 4),
	'PmaCI'     => array('CACGTG', 3),
	'PmeI'      => array('GTTTAAAC', 4),
	'PmlI'      => array('CACGTG', 3),
	'Ppu10I'    => array('ATGCAT', 1),
	'PpuMI'     => array('RGGWCCY', 2),
	'PpuXI'     => array('RGGWCCY', 2),
	'PshAI'     => array('GACNNNNGTC', 5),
	'PshBI'     => array('ATTAAT', 2),
	'PsiI'      => array('TTATAA', 3),
	'Psp03I'    => array('GGWCC', 4),
	'Psp5II'    => array('RGGWCCY', 2),
	'Psp6I'     => array('CCWGG', 0),
	'Psp1406I'  => array('AACGTT', 2),
	'PspAI'     => array('CCCGGG', 1),
	'Psp124BI'  => array('GAGCTC', 5),
	'PspEI'     => array('GGTNACC', 1),
	'PspGI'     => array('CCWGG', 0),
	'PspLI'     => array('CGTACG', 1),
	'PspN4I'    => array('GGNNCC', 3),
	'PspOMI'    => array('GGGCCC', 1),
	'PspPI'     => array('GGNCC', 1),
	'PspPPI'    => array('RGGWCCY', 2),
	'PssI'      => array('RGGNCCY', 5),
	'PstI'      => array('CTGCAG', 5),
	'PsuI'      => array('RGATCY', 1),
	'PsyI'      => array('GACNNNGTC', 4),
	'PvuI'      => array('CGATCG', 4),
	'PvuII'     => array('CAGCTG', 3),
	'RcaI'      => array('TCATGA', 1),
	'RsaI'      => array('GTAC', 2),
	'RsrII'     => array('CGGWCCG', 2),
	'Rsr2I'     => array('CGGWCCG', 2),
	'SacI'      => array('GAGCTC', 5),
	'SacII'     => array('CCGCGG', 4),
	'SalI'      => array('GTCGAC', 1),
	'SanDI'     => array('GGGWCCC', 2),
	'SatI'      => array('GCNGC', 2),
	'SauI'      => array('CCTNAGG', 2),
	'Sau96I'    => array('GGNCC', 1),
	'Sau3AI'    => array('GATC', 0),
	'SbfI'      => array('CCTGCAGG', 6),
	'ScaI'      => array('AGTACT', 3),
	'SciI'      => array('CTCGAG', 3),
	'ScrFI'     => array('CCNGG', 2),
	'SdaI'      => array('CCTGCAGG', 6),
	'SduI'      => array('GDGCHC', 5),
	'SecI'      => array('CCNNGG', 1),
	'SelI'      => array('CGCG', 0),
	'SexAI'     => array('ACCWGGT', 1),
	'SfcI'      => array('CTRYAG', 1),
	'SfeI'      => array('CTRYAG', 1),
	'SfiI'      => array('GGCCNNNNNGGCC', 8),
	'SfoI'      => array('GGCGCC', 3),
	'Sfr274I'   => array('CTCGAG', 1),
	'Sfr303I'   => array('CCGCGG', 4),
	'SfuI'      => array('TTCGAA', 2),
	'SgfI'      => array('GCGATCGC', 5),
	'SgrAI'     => array('CRCCGGYG', 2),
	'SgrBI'     => array('CCGCGG', 4),
	'SinI'      => array('GGWCC', 1),
	'SlaI'      => array('CTCGAG', 1),
	'SmaI'      => array('CCCGGG', 3),
	'SmiI'      => array('ATTTAAAT', 4),
	'SmiMI'     => array('CAYNNNNRTG', 5),
	'SmlI'      => array('CTYRAG', 1),
	'SnaBI'     => array('TACGTA', 3),
	'SpaHI'     => array('GCATGC', 5),
	'SpeI'      => array('ACTAGT', 1),
	'SphI'      => array('GCATGC', 5),
	'SplI'      => array('CGTACG', 1),
	'SrfI'      => array('GCCCGGGC', 4),
	'Sse9I'     => array('AATT', 0),
	'Sse232I'   => array('CGCCGGCG', 2),
	'Sse8387I'  => array('CCTGCAGG', 6),
	'Sse8647I'  => array('AGGWCCT', 2),
	'SseBI'     => array('AGGCCT', 3),
	'SspI'      => array('AATATT', 3),
	'SspBI'     => array('TGTACA', 1),
	'SstI'      => array('GAGCTC', 5),
	'SstII'     => array('CCGCGG', 4),
	'StuI'      => array('AGGCCT', 3),
	'StyI'      => array('CCWWGG', 1),
	'SunI'      => array('CGTACG', 1),
	'SwaI'      => array('ATTTAAAT', 4),
	'TaaI'      => array('ACNGT', 3),
	'TaiI'      => array('ACGT', 4),
	'TaqI'      => array('TCGA', 1),
	'TasI'      => array('AATT', 0),
	'TatI'      => array('WGTACW', 1),
	'TauI'      => array('GCSGC', 4),
	'TelI'      => array('GACNNNGTC', 4),
	'TfiI'      => array('GAWTC', 1),
	'ThaI'      => array('CGCG', 2),
	'TliI'      => array('CTCGAG', 1),
	'Tru1I'     => array('TTAA', 1),
	'Tru9I'     => array('TTAA', 1),
	'TscI'      => array('ACGT', 4),
	'TseI'      => array('GCWGC', 1),
	'Tsp45I'    => array('GTSAC', 0),
	'Tsp509I'   => array('AATT', 0),
	'Tsp4CI'    => array('ACNGT', 3),
	'TspEI'     => array('AATT', 0),
	'Tth111I'   => array('GACNNNGTC', 4),
	'TthHB8I'   => array('TCGA', 1),
	'UnbI'      => array('GGNCC', 0),
	'Van91I'    => array('CCANNNNNTGG', 7),
	'Vha464I'   => array('CTTAAG', 1),
	'VneI'      => array('GTGCAC', 1),
	'VpaK11AI'  => array('GGWCC', 0),
	'VpaK11BI'  => array('GGWCC', 1),
	'VspI'      => array('ATTAAT', 2),
	'XagI'      => array('CCTNNNNNAGG', 5),
	'XapI'      => array('RAATTY', 1),
	'XbaI'      => array('TCTAGA', 1),
	'XceI'      => array('RCATGY', 5),
	'XcmI'      => array('CCANNNNNNNNNTGG', 8),
	'XhoI'      => array('CTCGAG', 1),
	'XhoII'     => array('RGATCY', 1),
	'XmaI'      => array('CCCGGG', 1),
	'XmaIII'    => array('CGGCCG', 1),
	'XmaCI'     => array('CCCGGG', 1),
	'XmaJI'     => array('CCTAGG', 1),
	'XmiI'      => array('GTMKAC', 2),
	'XmnI'      => array('GAANNNNTTC', 5),
	'XspI'      => array('CTAG', 1),
	'ZhoI'      => array('ATCGAT', 2),
	'ZraI'      => array('GACGTC', 3),
	'Zsp2I'     => array('ATGCAT', 5)
	);

class RestEn
{ // OPENS RestEn Class
var $name;
var $pattern;
var $cutpos;
var $length;

// CutSeq() cuts a DNA sequence into fragments using the restriction enzyme object.
function CutSeq($seq, $options = "N")
	{ // OPENS function CutSeq
	if ($options == "N")
		{ // OPENS if ($options == "N") 
		// patpos() returns: ( "PAT1" => (0, 12), "PAT2" => (7, 29, 53) )
		$patpos_r = $seq->patpos($this->pattern, "I");
		$frag = array();
		foreach($patpos_r as $patkey => $pos_r)
			{
			$ctr = 0;
			foreach($pos_r as $currindex)
				{
				$ctr++;
				if ($ctr == 1)
					{
					// 1st fragment is everything to the left of the 1st occurrence of pattern
					$frag[] = substr($seq->sequence, 0, $currindex + $this->cutpos);
					$previndex = $currindex;
					continue;
					}
				if (($currindex - $previndex) >= $this->cutpos)
					{
					$newcount = $currindex - $previndex;
					$frag[] = substr($seq->sequence, $previndex + $this->cutpos, $newcount);
					$previndex = $currindex;
					}
				else continue;
				}
			// The last (right-most) fragment.
			$frag[] = substr($seq->sequence, $previndex + $this->cutpos);
			} 
		return $frag;
		} // CLOSES if ($options == "N")
	elseif ($options == "O")
		{ // OPENS elseif ($options == "O")
		$pos_r = $seq->patposo($this->pattern, "I", $this->cutpos);
		$ctr = 0;
		foreach($pos_r as $currindex)
			{
			$ctr++;
			if ($ctr == 1)
				{
				$frag[] = substr($seq->sequence, 0, $currindex + $this->cutpos);
				$previndex = $currindex;
				continue;
				}
			if (($currindex - $previndex) >= $this->cutpos)
				{
				$newcount = $currindex - $previndex;
				$frag[] = substr($seq->sequence, $previndex + $this->cutpos, $newcount);
				$previndex = $currindex;
				}
			else continue;
			}
		// The last (right-most) fragment.
		$frag[] = substr($seq->sequence, $previndex + $this->cutpos);
		return $frag;
		} // CLOSES elseif ($options == "O")
	} // CLOSES function CutSeq

/* 
RestEn() is the constructor method for the RestEn class.  It creates a new
RestEn object and initializes its properties accordingly.

RestEn() behavior:
If passed with make = 'custom', object will be added to RestEn_DB.
If not, the function will attemp to retrieve data from RestEn_DB.
If unsuccessful in retrieving data, it will return an error flag.
*/
function RestEn($args)
	{
	global $RestEn_DB;

	$arguments = parse_args($args);

	if ($arguments["make"] == "custom")
		{
		$this->name = $arguments["name"];
		$this->pattern = $arguments["pattern"];
		$this->cutpos = $arguments["cutpos"];
		$this->length = strlen($this->pattern);

		$inner = array();
		$inner[] = $arguments["pattern"];
		$inner[] = $arguments["cutpos"];
		$RestEn_DB[$this->name] = $inner;
		}
	else
		{
		// Look for given endonuclease in the RestEn_DB array.

		$this->name = $arguments["name"];
		$temp = $this->GetPattern($this->name);
		if ($temp == FALSE)
			die("Cannot find entry in restriction endonuclease database.");
		else
			{
			$this->pattern = $temp;
			$this->cutpos = $this->GetCutPos($this->name);
			$this->length = strlen($this->pattern);
			}
		}
	}

// GetPattern() returns the pattern associated with a given restriction endonuclease.
function GetPattern($RestEn_Name)
	{
	global $RestEn_DB;
	return $RestEn_DB[$RestEn_Name][0];
	}

// GetCutPos() returns the cutting position of the restriction enzyme object.
function GetCutPos($RestEn_Name)
	{
	global $RestEn_DB;
	return $RestEn_DB[$RestEn_Name][1];
	}

// GetLength() returns the length of the cutting pattern of the restriction enzyme object.
function GetLength($RestEn_Name = "")
	{
	global $RestEn_DB;

	if ($RestEn_Name == "") return strlen($this->pattern);
	else return strlen($RestEn_DB[$RestEn_Name][0]);
	}

// FindRestEn() is a flexible method for searching the Restriction Enzyme database 
// for entries meeting complex criteria.  It returns an array of RestEn objects.
function FindRestEn($pattern = "", $cutpos = "", $plen = "")
	{ // OPENS function FindRestEn().
	global $RestEn_DB;

	// 5 Cases: pattern only, cutpos only, patternlength only
	//          pattern and cutpos, cutpos and patternlength
	$RestEn_List = array();

	// Case 1: Pattern only
	if (($pattern != "") and ($cutpos == "") and ($plen == ""))
		{
		foreach($RestEn_DB as $key => $value)
			if ($value[0] == $pattern)
			$RestEn_List[] = $key;
		return $RestEn_List;
		}

	// Case 2: Cutpos only
	if (($pattern == "") and ($cutpos != "") and ($plen == ""))
		{ // OPENS if (($pattern == "") and ($cutpos != "") and ($plen == ""))
		$firstchar = substr($cutpos, 0, 1);
		$first2chars = substr($cutpos, 0, 2);
		if (gettype($cutpos) == "string")
			{ // OPENS if (gettype($cutpos) == "string") 
			if (preg_match("/^<\d+$/", $cutpos))
				{
				foreach($RestEn_DB as $key => $value)
				 if ($value[1] < (int) substr($cutpos,1))
					 $RestEn_List[] = $key;
				return $RestEn_List;
				}
			elseif (preg_match("/^>\d+$/", $cutpos))
				{
				foreach($RestEn_DB as $key => $value)
					if ($value[1] > (int) substr($cutpos,1))
						$RestEn_List[] = $key;
				return $RestEn_List;
				}
			elseif (preg_match("/^>=\d+$/", $cutpos))
				{
				foreach($RestEn_DB as $key => $value)
					if ($value[1] >= (int) substr($cutpos,2))
						$RestEn_List[] = $key;
				return $RestEn_List;
				}
			elseif (preg_match("/^<=\d+$/", $cutpos))
				{
				foreach($RestEn_DB as $key => $value)
					if ($value[1] <= (int) substr($cutpos,2))
						$RestEn_List[] = $key;
				return $RestEn_List;
				}
			elseif (preg_match("/^=\d+$/", $cutpos))
				{
				foreach($RestEn_DB as $key => $value)
					if ($value[1] == substr($cutpos,1))
						$RestEn_List[] = $key;
				return $RestEn_List;
				}
			else die("Malformed cutpos parameter.");
			} // CLOSES if (gettype($cutpos) == "string")
		elseif (gettype($cutpos) == "integer")
			{
			foreach($RestEn_DB as $key => $value)
				if ($value[1] == $cutpos)
					$RestEn_List[] = $key;
			return $RestEn_List;
			}
		} // CLOSES if (($pattern == "") and ($cutpos != "") and ($plen == ""))

	// Case 3: Patternlength only
	if (($pattern == "") and ($cutpos == "") and ($plen != ""))
		{
		foreach($RestEn_DB as $key => $value)
			if (strlen($value[0]) == $plen)
				$RestEn_List[] = $key;
		return $RestEn_List;
		}

	// Case 4: Pattern and cutpos only
	if (($pattern != "") and ($cutpos != "") and ($plen == ""))
		{
		foreach($RestEn_DB as $key => $value)
			if (($value[0] == $pattern) and ($value[1] == $cutpos))
				$RestEn_List[] = $key;
		return $RestEn_List;
		}

	// Case 5: Cutpos and plen only.
	if (($pattern == "") and ($cutpos != "") and ($plen != ""))
		{
		foreach($RestEn_DB as $key => $value)
			if (($value[1] == $cutpos) and (strlen($value[0]) == $plen))
				$RestEn_List[] = $key;
		return $RestEn_List;
		}

	die("Invalid combination of function parameters.");
	} // CLOSES function FindRestEn().
} // CLOSES RestEn Class
?>