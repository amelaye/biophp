<?php
namespace Tests\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\DTO\AminoDTO;

$aAminosObjects = [];
$amino = new AminoDTO();
$amino->setId('A');
$amino->setName("Alanine");
$amino->setName1Letter('A');
$amino->setName3Letters('Ala');
$amino->setWeight1(89.0932);
$amino->setWeight2(89.0932);
$amino->setResidueMolWeight(71.0779);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('B');
$amino->setName("Aspartate or asparagine");
$amino->setName1Letter('B');
$amino->setName3Letters('N/A');
$amino->setWeight1(132.1179);
$amino->setWeight2(133.1027);
$amino->setResidueMolWeight(114.1026);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('C');
$amino->setName("Cysteine");
$amino->setName1Letter('C');
$amino->setName3Letters('Cys');
$amino->setWeight1(121.1582);
$amino->setWeight2(121.1582);
$amino->setResidueMolWeight(103.1429);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('D');
$amino->setName("Aspartic acid");
$amino->setName1Letter('D');
$amino->setName3Letters('Asp');
$amino->setWeight1(133.1027);
$amino->setWeight2(133.1027);
$amino->setResidueMolWeight(115.0874);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('E');
$amino->setName("Glutamic acid");
$amino->setName1Letter('E');
$amino->setName3Letters('Glu');
$amino->setWeight1(147.1293);
$amino->setWeight2(147.1293);
$amino->setResidueMolWeight(129.114);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('F');
$amino->setName("Phenylalanine");
$amino->setName1Letter('F');
$amino->setName3Letters('Phe');
$amino->setWeight1(165.1891);
$amino->setWeight2(165.1891);
$amino->setResidueMolWeight(147.1738);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('G');
$amino->setName("Glycine");
$amino->setName1Letter('G');
$amino->setName3Letters('Gly');
$amino->setWeight1(75.0666);
$amino->setWeight2(75.0666);
$amino->setResidueMolWeight(57.0513);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('H');
$amino->setName("Histidine");
$amino->setName1Letter('H');
$amino->setName3Letters('His');
$amino->setWeight1(155.1546);
$amino->setWeight2(155.1546);
$amino->setResidueMolWeight(137.1393);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('I');
$amino->setName("Isoleucine");
$amino->setName1Letter('I');
$amino->setName3Letters('Ile');
$amino->setWeight1(131.1729);
$amino->setWeight2(131.1729);
$amino->setResidueMolWeight(113.1576);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('K');
$amino->setName("Lysine");
$amino->setName1Letter('K');
$amino->setName3Letters('Lys');
$amino->setWeight1(146.1876);
$amino->setWeight2(146.1876);
$amino->setResidueMolWeight(128.1723);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('L');
$amino->setName("Leucine");
$amino->setName1Letter('L');
$amino->setName3Letters('Leu');
$amino->setWeight1(131.1729);
$amino->setWeight2(131.1729);
$amino->setResidueMolWeight(113.1576);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('M');
$amino->setName("Methionine");
$amino->setName1Letter('M');
$amino->setName3Letters('Met');
$amino->setWeight1(149.2113);
$amino->setWeight2(149.2113);
$amino->setResidueMolWeight(131.196);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('N');
$amino->setName("Asparagine");
$amino->setName1Letter('N');
$amino->setName3Letters('Asn');
$amino->setWeight1(132.1179);
$amino->setWeight2(132.1179);
$amino->setResidueMolWeight(114.1026);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('O');
$amino->setName("Pyrrolysine");
$amino->setName1Letter('O');
$amino->setName3Letters('Pyl');
$amino->setWeight1(255.3134);
$amino->setWeight2(255.3134);
$amino->setResidueMolWeight(237.2981);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('P');
$amino->setName("Proline");
$amino->setName1Letter('P');
$amino->setName3Letters('Pro');
$amino->setWeight1(115.1305);
$amino->setWeight2(115.1305);
$amino->setResidueMolWeight(97.1152);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('Q');
$amino->setName("Glutamine");
$amino->setName1Letter('Q');
$amino->setName3Letters('Gln');
$amino->setWeight1(146.1445);
$amino->setWeight2(146.1445);
$amino->setResidueMolWeight(128.1292);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('R');
$amino->setName("Arginine");
$amino->setName1Letter('R');
$amino->setName3Letters('Arg');
$amino->setWeight1(174.201);
$amino->setWeight2(174.201);
$amino->setResidueMolWeight(156.1857);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('S');
$amino->setName("Serine");
$amino->setName1Letter('S');
$amino->setName3Letters('Ser');
$amino->setWeight1(105.0926);
$amino->setWeight2(105.0926);
$amino->setResidueMolWeight(87.0773);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('T');
$amino->setName("Threonine");
$amino->setName1Letter('T');
$amino->setName3Letters('Thr');
$amino->setWeight1(119.1192);
$amino->setWeight2(119.1192);
$amino->setResidueMolWeight(101.1039);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('U');
$amino->setName("Selenocysteine");
$amino->setName1Letter('U');
$amino->setName3Letters('Sec');
$amino->setWeight1(168.0532);
$amino->setWeight2(168.0532);
$amino->setResidueMolWeight(150.0379);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('V');
$amino->setName("Valine");
$amino->setName1Letter('V');
$amino->setName3Letters('Val');
$amino->setWeight1(117.1463);
$amino->setWeight2(117.1463);
$amino->setResidueMolWeight(99.131);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('W');
$amino->setName("Tryptophan");
$amino->setName1Letter('W');
$amino->setName3Letters('Trp');
$amino->setWeight1(204.2252);
$amino->setWeight2(204.2252);
$amino->setResidueMolWeight(186.2099);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('Y');
$amino->setName("Tyrosine");
$amino->setName1Letter('Y');
$amino->setName3Letters('Tyr');
$amino->setWeight1(181.1885);
$amino->setWeight2(181.1885);
$amino->setResidueMolWeight(163.1732);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('Z');
$amino->setName("Glutamate or glutamine");
$amino->setName1Letter('Z');
$amino->setName3Letters('N/A');
$amino->setWeight1(146.1445);
$amino->setWeight2(147.1293);
$amino->setResidueMolWeight(128.1292);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('X');
$amino->setName("Any");
$amino->setName1Letter('X');
$amino->setName3Letters('XXX');
$amino->setWeight1(75.0666);
$amino->setWeight2(204.2252);
$amino->setResidueMolWeight(114.822);
$aAminosObjects[] = $amino;

$amino = new AminoDTO();
$amino->setId('*');
$amino->setName("STOP");
$amino->setName1Letter('*');
$amino->setName3Letters('STP');
$amino->setWeight1(0);
$amino->setWeight2(0);
$amino->setResidueMolWeight(0);
$aAminosObjects[] = $amino;