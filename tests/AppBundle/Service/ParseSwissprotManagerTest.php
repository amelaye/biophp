<?php


namespace Tests\AppBundle\Service;


use AppBundle\Entity\IO\Collection;
use AppBundle\Entity\IO\CollectionElement;
use AppBundle\Entity\Sequence;
use AppBundle\Service\IO\DatabaseManager;
use AppBundle\Service\IO\ParseGenbankManager;
use AppBundle\Service\IO\ParseSwissprotManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;

class ParseSwissprotManagerTest extends TestCase
{
    /*public function testFetch()
    {
        $collection = new Collection();
        $collection->setId(3);
        $collection->setNomCollection("humandbSwiss");

        $collectionRepository = $this->createMock(Collection::class);
        $collectionRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturn($collection);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($collectionRepository);

        $parseGenbankManager = new ParseGenbankManager();
        $parseSwissProtManager = new ParseSwissprotManager();

        $databaseManager = new DatabaseManager($collectionRepository, $parseGenbankManager, $parseSwissProtManager);
        $databaseManager->recording("humandbSwiss", "SWISSPROT", "basicswiss.txt");
        $oSequence = $databaseManager->fetch("1375");

        $oExpected = new Sequence();
        $oExpected->setId("FA");
        $oExpected->setMoltype("PRT");
        $oExpected->setDate("21-JUL-1986");
        $oExpected->setDefinition("TUMOR NECROSIS FACTOR PRECURSOR (TNF-ALPHA) (CACHECTIN).");
        $oExpected->setSeqlength(233);
        $oExpected->setAccession("P01375");
        $keywords = [
            0 => "CYTOKINE",
            1 => "CYTOTOXIN",
            2 => "TRANSMEMBRANE",
            3 => "GLYCOPROTEIN",
            4 => "SIGNAL-ANCHOR",
            5 => "MYRISTYLATION",
            6 => "3D-STRUCTURE",
        ];
        $oExpected->setKeywords($keywords);
        $source = [0 => " HOMO SAPIENS (HUMAN)"];
        $oExpected->setSource($source);
        $organism = [
            0 => "EUKARYOTA",
            1 => "METAZOA",
            2 => "CHORDATA",
            3 => "VERTEBRATA",
            4 => "TETRAPODA",
            5 => "MAMMALIA",
            6 => "EUTHERIA",
            7 => "PRIMATES",
        ];
        $oExpected->setOrganism($organism);
        $sequence = "MSTESMIRDVELAEEALPKKTGGPQGSRRCLFLSLFSFLIVAGATTLFCLLHFGVIGPQREEFPRDLSLISPLAQAVRSSSRTPSDKPVAHVVANP";
        $sequence.= "QAEGQLQWLNRRANALLANGVELRDNQLVVPSEGLYLIYSQVLFKGQGCPSTHVLLTHTISRIAVSYQTKVNLLSAIKSPCQRETPEGAEAKPWYE";
        $sequence.= "PIYLGGVFQLEKGDRLSAEINRPDYLDFAESGQVYFGIIAL";
        $oExpected->setSequence($sequence);
        $reference = [
            0 => [
              "REFNO" => 0,
              "MEDLINE" => "87217060",
              "REMARKS" => "SEQUENCE FROM N.A.",
              "COMMENT" => null,
              "TITLE" => "COLD SPRING HARB. SYMP. QUANT. BIOL. 51:611-624(1986).",
              "JOURNAL" => "COLD SPRING HARB. SYMP. QUANT. BIOL. 51:611-624(1986).",
              "AUTHORS" => [
                0 => "CHUMAKOV A.M.",
                1 => "SHINGAROVA L.N.",
                2 => "OVCHINNIKOV Y.A."
              ]
            ],
            1 => [
              "REFNO" => 1,
              "MEDLINE" => "85086244",
              "REMARKS" => "SEQUENCE FROM N.A.",
              "COMMENT" => null,
              "TITLE" => "NATURE 312:724-729(1984).",
              "JOURNAL" => "NATURE 312:724-729(1984).",
              "AUTHORS" => [
                0 => "PALLADINO M.A.",
                1 => "KOHR W.J.",
                2 => "AGGARWAL B.B.",
                3 => "GOEDDEL D.V."
              ]
            ],
            2 => [
              "REFNO" => 2,
              "MEDLINE" => "85137898",
              "REMARKS" => "SEQUENCE FROM N.A.",
              "COMMENT" => null,
              "TITLE" => "NATURE 313:803-806(1985).",
              "JOURNAL" => "NATURE 313:803-806(1985).",
              "AUTHORS" => [
                0 => "SHIRAI T.",
                1 => "YAMAGUCHI H.",
                2 => "ITO H.",
                3 => "TODD C.W.",
                4 => "WALLACE R.B.",
              ]
            ],
            3 => [
              "REFNO" => 3,
              "MEDLINE" => "86016093",
              "REMARKS" => "SEQUENCE FROM N.A.",
              "COMMENT" => null,
              "TITLE" => "NUCLEIC ACIDS RES. 13:6361-6373(1985).",
              "JOURNAL" => "NUCLEIC ACIDS RES. 13:6361-6373(1985).",
              "AUTHORS" => [
                0 => "JARRETT-NEDWIN J.",
                1 => "PENNICA D.",
                2 => "GOEDDEL D.V.",
                3 => "GRAY P.W.",
              ]
            ],
            4 => [
              "REFNO" => 4,
              "MEDLINE" => "85142190",
              "REMARKS" => "SEQUENCE FROM N.A.",
              "COMMENT" => null,
              "TITLE" => "SCIENCE 228:149-154(1985).",
              "JOURNAL" => "SCIENCE 228:149-154(1985).",
              "AUTHORS" => [
                0 => "VAN ARSDELL J.N.",
                1 => "YAMAMOTO R.",
                2 => "MARK D.F."
              ]
            ],
            5 => [
              "REFNO" => 5,
              "MEDLINE" => "90008932",
              "REMARKS" => "X-RAY CRYSTALLOGRAPHY (2.6 ANGSTROMS).",
              "COMMENT" => null,
              "TITLE" => "J. BIOL. CHEM. 264:17595-17605(1989).",
              "JOURNAL" => "J. BIOL. CHEM. 264:17595-17605(1989).",
              "AUTHORS" => [
                0 => "ECK M.J.",
                1 => "SPRANG S.R.",
              ]
            ],
            6 => [
              "REFNO" => 6,
              "MEDLINE" => "91193276",
              "REMARKS" => "X-RAY CRYSTALLOGRAPHY (2.9 ANGSTROMS).",
              "COMMENT" => null,
              "TITLE" => "J. CELL SCI. SUPPL. 13:11-18(1990).",
              "JOURNAL" => "J. CELL SCI. SUPPL. 13:11-18(1990).",
              "AUTHORS" => [
                0 => "JONES E.Y.",
                1 => "STUART D.I.",
                2 => "WALKER N.P."
              ]
            ],
            7 => [
              "REFNO" => 7,
              "MEDLINE" => "90008932",
              "REMARKS" => "X-RAY CRYSTALLOGRAPHY (2.6 ANGSTROMS).",
              "COMMENT" => null,
              "TITLE" => "J. BIOL. CHEM. 264:17595-17605(1989).",
              "JOURNAL" => "J. BIOL. CHEM. 264:17595-17605(1989).",
              "AUTHORS" => [
                0 => "ECK M.J.",
                1 => "SPRANG S.R.",
              ]
            ],
            8 => [
              "REFNO" => 8,
              "MEDLINE" => "91184128",
              "REMARKS" => "MUTAGENESIS.",
              "COMMENT" => null,
              "TITLE" => "EMBO J. 10:827-836(1991).",
              "JOURNAL" => "EMBO J. 10:827-836(1991).",
              "AUTHORS" => [
                0 => "OSTADE X.V.",
                1 => "TAVERNIER J.",
                2 => "PRANGE T.",
                3 => "FIERS W.",
              ]
            ],
            9 => [
              "REFNO" => 9,
              "MEDLINE" => "93018820",
              "REMARKS" => "MYRISTOYLATION.",
              "COMMENT" => null,
              "TITLE" => "J. EXP. MED. 176:1053-1062(1992).",
              "JOURNAL" => "J. EXP. MED. 176:1053-1062(1992).",
              "AUTHORS" => [
                0 => "STEVENSON F.T.",
                1 => "BURSTEN S.L.",
                2 => "LOCKSLEY R.M.",
                3 => "LOVETT D.H.",
              ]
            ]
          ];
        $oExpected->setReference($reference);
        $swissprot = [
            "ID" => "FA",
            "PROT_NAME" => "FA",
            "MOL_TYPE" => "PRT;",
            "PROT_SOURCE" => "HUMAN",
            "DATA_CLASS" => "STANDARD;",
            "LENGTH" => 233,
            "CREATE_DATE" => "21-JUL-1986",
            "CREATE_REL" => "01",
            "SEQUPD_DATE" => "21-JUL-1986",
            "SEQUPD_REL" => "01",
            "NOTUPD_DATE" => "01-FEB-1995",
            "NOTUPD_REL" => "31",
            "IS_FRAGMENT" => false,
            "ORGANISM" => [
              0 => " HOMO SAPIENS (HUMAN)"
            ],
            "ORG_CLASS" => [
              0 => "EUKARYOTA",
              1 => "METAZOA",
              2 => "CHORDATA",
              3 => "VERTEBRATA",
              4 => "TETRAPODA",
              5 => "MAMMALIA",
              6 => "EUTHERIA",
              7 => "PRIMATES",
            ],
            "AMINO_COUNT" => 233,
            "MOLWT" => 25644,
            "CHK_NO" => "279986",
            "CHK_METHOD" => "CN",
            "SEQUENCE" => "MSTESMIRDVELAEEALPKKTGGPQGSRRCLFLSLFSFLIVAGATTLFCLLHFGVIGPQREEFPRDLSLISPLAQAVRSSSRTPSDKPVAHVVANPQAEGQLQWLNRRANALLANGVELRDNQLVVPSEGLYLIYSQVLFKGQGCPSTHVLLTHTISRIAVSYQTKVNLLSAIKSPCQRETPEGAEAKPWYEPIYLGGVFQLEKGDRLSAEINRPDYLDFAESGQVYFGIIAL",
            "ACCESSION" => [
              0 => "P01375"
            ],
            "PRIM_AC" => "P01375",
            "DESC" => "TUMOR NECROSIS FACTOR PRECURSOR (TNF-ALPHA) (CACHECTIN).",
            "KEYWORDS" => [
              0 => "CYTOKINE",
              1 => "CYTOTOXIN",
              2 => "TRANSMEMBRANE",
              3 => "GLYCOPROTEIN",
              4 => "SIGNAL-ANCHOR",
              5 => "MYRISTYLATION",
              6 => "3D-STRUCTURE",
            ],
            "ORGANELLE" => null,
            "FT_PROPEP" => [
              0 => [
                0 => 1,
                1 => 76,
                2 => false
              ]
            ],
            "FT_CHAIN" => [
              0 => [
                0 => 77,
                1 => 233,
                2 => "TUMOR NECROSIS FACTOR",
              ]
            ],
            "FT_TRANSMEM" => [
              0 => [
                0 => 36,
                1 => 56,
                2 => "SIGNAL-ANCHOR (TYPE-II PROTEIN)",
              ]
            ],
            "FT_LIPID" => [
              0 => [
                0 => 19,
                1 => 19,
                2 => "MYRISTATE",
              ],
              1 => [
                0 => 20,
                1 => 20,
                2 => "MYRISTATE",
              ]
            ],
            "FT_DISULFID" => [
              0 => [
                0 => 145,
                1 => 177,
                2 => false,
              ]
            ],
            "FT_MUTAGEN" => [
              0 => [
                0 => 108,
                1 => 108,
                2 => "R->W: BIOLOGICALLY INACTIVE",
              ],
              1 => [
                0 => 112,
                1 => 112,
                2 => "L->F: BIOLOGICALLY INACTIVE",
              ],
              2 => [
                0 => 162,
                1 => 162,
                2 => "S->F: BIOLOGICALLY INACTIVE",
              ],
              3 => [
                0 => 167,
                1 => 167,
                2 => "V->A,D: BIOLOGICALLY INACTIVE",
              ],
              4 => [
                0 => 222,
                1 => 222,
                2 => "E->K: BIOLOGICALLY INACTIVE",
              ],
            ],
            "FT_CONFLICT" => [
              0 => [
                0 => 63,
                1 => 63,
                2 => "F -> S (IN REF. 5)",
              ]
            ],
            "FT_STRAND" => [
              0 => [
                0 => 89,
                1 => 93,
                2 => false
              ],
              1 => [
                0 => 112,
                1 => 113,
                2 => false
              ],
              2 => [
                0 => 118,
                1 => 119,
                2 => false
              ],
              3 => [
                0 => 124,
                1 => 125,
                2 => false
              ],
              4 => [
                0 => 130,
                1 => 143,
                2 => false
              ],
              5 => [
                0 => 152,
                1 => 159,
                2 => false
              ],
              6 => [
                0 => 166,
                1 => 170,
                2 => false
              ],
              7 => [
                0 => 173,
                1 => 174,
                2 => false
              ],
              8 => [
                0 => 189,
                1 => 202,
                2 => false
              ],
              9 => [
                0 => 207,
                1 => 212,
                2 => false
              ],
              10 => [
                0 => 218,
                1 => 218,
                2 => false
              ],
              11 => [
                0 => 227,
                1 => 232,
                2 => false
              ],
            ],
            "FT_TURN" => [
              0 => [
                0 => 99,
                1 => 100,
                2 => false
              ],
              1 => [
                0 => 109,
                1 => 110,
                2 => false
              ],
              2 => [
                0 => 115,
                1 => 116,
                2 => false
              ],
              3 => [
                0 => 183,
                1 => 184,
                2 => false
              ],
              4 => [
                0 => 204,
                1 => 205,
                2 => false
              ],
            ],
            "FT_HELIX" => [
              0 => [
                0 => 215,
                1 => 217,
                2 => false
              ],
            ],
            "GENE_NAME" => [
              0 => [
                0 => "TNFA"
              ]
            ],
            "REFERENCE" => [
              0 => [
                "RP" => "SEQUENCE FROM N.A.",
                "RX_BDN" => "MEDLINE",
                "RX_ID" => "87217060",
                "RA" => [
                  0 => "CHUMAKOV A.M.",
                  1 => "SHINGAROVA L.N.",
                  2 => "OVCHINNIKOV Y.A.",
                ],
                "RL" => "COLD SPRING HARB. SYMP. QUANT. BIOL. 51:611-624(1986)."
              ],
              1 => [
                "RP" => "SEQUENCE FROM N.A.",
                "RX_BDN" => "MEDLINE",
                "RX_ID" => "85086244",
                "RA" => [
                  0 => "PALLADINO M.A.",
                  1 => "KOHR W.J.",
                  2 => "AGGARWAL B.B.",
                  3 => "GOEDDEL D.V.",
                ],
                "RL" => "NATURE 312:724-729(1984)."
              ],
              2 => [
                "RP" => "SEQUENCE FROM N.A.",
                "RX_BDN" => "MEDLINE",
                "RX_ID" => "85137898",
                "RA" => [
                  0 => "SHIRAI T.",
                  1 => "YAMAGUCHI H.",
                  2 => "ITO H.",
                  3 => "TODD C.W.",
                  4 => "WALLACE R.B.",
                ],
                "RL" => "NATURE 313:803-806(1985).",
              ],
              3 => [
                "RP" => "SEQUENCE FROM N.A.",
                "RX_BDN" => "MEDLINE",
                "RX_ID" => "86016093",
                "RA" => [
                  0 => "JARRETT-NEDWIN J.",
                  1 => "PENNICA D.",
                  2 => "GOEDDEL D.V.",
                  3 => "GRAY P.W.",
                ],
                "RL" => "NUCLEIC ACIDS RES. 13:6361-6373(1985)."
              ],
              4 => [
                "RP" => "SEQUENCE FROM N.A.",
                "RX_BDN" => "MEDLINE",
                "RX_ID" => "85142190",
                "RA" => [
                  0 => "VAN ARSDELL J.N.",
                  1 => "YAMAMOTO R.",
                  2 => "MARK D.F.",
                ],
                "RL" => "SCIENCE 228:149-154(1985).",
              ],
              5 => [
                "RP" => "X-RAY CRYSTALLOGRAPHY (2.6 ANGSTROMS).",
                "RX_BDN" => "MEDLINE",
                "RX_ID" => "90008932",
                "RA" => [
                  0 => "ECK M.J.",
                  1 => "SPRANG S.R.",
                ],
                "RL" => "J. BIOL. CHEM. 264:17595-17605(1989).",
              ],
              6 => [
                "RP" => "X-RAY CRYSTALLOGRAPHY (2.9 ANGSTROMS).",
                "RX_BDN" => "MEDLINE",
                "RX_ID" => "91193276",
                "RA" => [
                  0 => "JONES E.Y.",
                  1 => "STUART D.I.",
                  2 => "WALKER N.P.",
                ],
                "RL" => "J. CELL SCI. SUPPL. 13:11-18(1990).",
              ],
              7 => [
                "RP" => "X-RAY CRYSTALLOGRAPHY (2.6 ANGSTROMS).",
                "RX_BDN" => "MEDLINE",
                "RX_ID" => "90008932",
                "RA" => [
                  0 => "ECK M.J.",
                  1 => "SPRANG S.R.",
                ],
                "RL" => "J. BIOL. CHEM. 264:17595-17605(1989).",
              ],
              8 => [
                "RP" => "MUTAGENESIS.",
                "RX_BDN" => "MEDLINE",
                "RX_ID" => "91184128",
                "RA" => [
                  0 => "OSTADE X.V.",
                  1 => "TAVERNIER J.",
                  2 => "PRANGE T.",
                  3 => "FIERS W.",
                ],
                "RL" => "EMBO J. 10:827-836(1991).",
              ],
              9 => [
                "RP" => "MYRISTOYLATION.",
                "RX_BDN" => "MEDLINE",
                "RX_ID" => "93018820",
                "RA" => [
                  0 => "STEVENSON F.T.",
                  1 => "BURSTEN S.L.",
                  2 => "LOCKSLEY R.M.",
                  3 => "LOVETT D.H.",
                ],
                "RL" => "J. EXP. MED. 176:1053-1062(1992).",
              ]
            ]
          ];
        $oExpected->setSwissprot($swissprot);

        $this->assertEquals($oExpected, $oSequence);
    }*/


}