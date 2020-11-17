<?php
namespace Tests\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\DTO\ProteinReductionDTO;

$aReductions = [];

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(20);
$reduction->setLetters("-");
$reduction->setPattern("-");
$reduction->setNature("-");
$reduction->setReduction("-");
$reduction->setDescription("Complete alphabet");
$aReductions[] = $reduction;

/**
 * 2
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(2);
$reduction->setLetters("PH");
$reduction->setPattern("A|G|T|S|N|Q|D|E|H|R|K|P");
$reduction->setNature("P: Hydrophilic");
$reduction->setReduction("p");
$reduction->setDescription("Two letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(2);
$reduction->setLetters("PH");
$reduction->setPattern("C|M|F|I|L|V|W|Y");
$reduction->setNature("H: Hydrophobic");
$reduction->setReduction("h");
$reduction->setDescription("Two letters alphabet");
$aReductions[] = $reduction;


/**
 * 5
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(5);
$reduction->setLetters("ARCTD");
$reduction->setPattern("I|V|L");
$reduction->setNature("A: Aliphatic");
$reduction->setReduction("a");
$reduction->setDescription("Five letters alphabet: Chemical / structural properties");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(5);
$reduction->setLetters("ARCTD");
$reduction->setPattern("F|Y|W|H");
$reduction->setNature("R: Aromatic");
$reduction->setReduction("r");
$reduction->setDescription("Five letters alphabet: Chemical / structural properties");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(5);
$reduction->setLetters("ARCTD");
$reduction->setPattern("K|R|D|E");
$reduction->setNature("C: Charged");
$reduction->setReduction("c");
$reduction->setDescription("Five letters alphabet: Chemical / structural properties");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(5);
$reduction->setLetters("ARCTD");
$reduction->setPattern("G|A|C|S");
$reduction->setNature("T: Tiny");
$reduction->setReduction("t");
$reduction->setDescription("Five letters alphabet: Chemical / structural properties");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(5);
$reduction->setLetters("ARCTD");
$reduction->setPattern("T|M|Q|N|P");
$reduction->setNature("D: Diverse");
$reduction->setReduction("d");
$reduction->setDescription("Five letters alphabet: Chemical / structural properties");
$aReductions[] = $reduction;

/**
 * 6
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(6);
$reduction->setLetters("ARPNTD");
$reduction->setPattern("I|V|L");
$reduction->setNature("A: Aliphatic");
$reduction->setReduction("a");
$reduction->setDescription("Six letters alphabet: Chemical / structural properties #2");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(6);
$reduction->setLetters("ARPNTD");
$reduction->setPattern("F|Y|W|H");
$reduction->setNature("R: Aromatic");
$reduction->setReduction("r");
$reduction->setDescription("Six letters alphabet: Chemical / structural properties #2");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(6);
$reduction->setLetters("ARPNTD");
$reduction->setPattern("K|R");
$reduction->setNature("C: Pos. charged");
$reduction->setReduction("p");
$reduction->setDescription("Six letters alphabet: Chemical / structural properties #2");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(6);
$reduction->setLetters("ARPNTD");
$reduction->setPattern("D|E");
$reduction->setNature("C: Neg. charged");
$reduction->setReduction("n");
$reduction->setDescription("Six letters alphabet: Chemical / structural properties #2");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(6);
$reduction->setLetters("ARPNTD");
$reduction->setPattern("G|A|C|S");
$reduction->setNature("T: Tiny");
$reduction->setReduction("t");
$reduction->setDescription("Six letters alphabet: Chemical / structural properties #2");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet(6);
$reduction->setLetters("ARPNTD");
$reduction->setPattern("T|M|Q|N|P");
$reduction->setNature("D: Diverse");
$reduction->setReduction("d");
$reduction->setDescription("Six letters alphabet: Chemical / structural properties #2");
$aReductions[] = $reduction;

/**
 * 3IMG
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("3IMG");
$reduction->setLetters("PNH");
$reduction->setPattern("D|N|E|Q|K|R");
$reduction->setNature("P: Hydrophilic");
$reduction->setReduction("p");
$reduction->setDescription("3 IMGT amino acid hydropathy alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("3IMG");
$reduction->setLetters("PNH");
$reduction->setPattern("G|T|S|Y|P|M");
$reduction->setNature("N: Neutral");
$reduction->setReduction("n");
$reduction->setDescription("3 IMGT amino acid hydropathy alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("3IMG");
$reduction->setLetters("PNH");
$reduction->setPattern("I|V|L|F|C|M|A|W");
$reduction->setNature("H: Hydrophobic");
$reduction->setReduction("h");
$reduction->setDescription("3 IMGT amino acid hydropathy alphabet");
$aReductions[] = $reduction;

/**
 * 5IMG
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("5IMG");
$reduction->setLetters("GCEMF");
$reduction->setPattern("G|A|S");
$reduction->setNature("G: 60-90");
$reduction->setReduction("g");
$reduction->setDescription("5 IMGT amino acid volume alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("5IMG");
$reduction->setLetters("GCEMF");
$reduction->setPattern("C|D|P|N|T");
$reduction->setNature("C: 108-117");
$reduction->setReduction("c");
$reduction->setDescription("5 IMGT amino acid volume alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("5IMG");
$reduction->setLetters("GCEMF");
$reduction->setPattern("E|V|Q|H");
$reduction->setNature("E: 138-154");
$reduction->setReduction("e");
$reduction->setDescription("5 IMGT amino acid volume alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("5IMG");
$reduction->setLetters("GCEMF");
$reduction->setPattern("M|I|L|K|R");
$reduction->setNature("M: 162-174");
$reduction->setReduction("m");
$reduction->setDescription("5 IMGT amino acid volume alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("5IMG");
$reduction->setLetters("GCEMF");
$reduction->setPattern("F|Y|W");
$reduction->setNature("F: 189-228");
$reduction->setReduction("f");
$reduction->setDescription("5 IMGT amino acid volume alphabet");
$aReductions[] = $reduction;


/**
 * 11IMG
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("A|V|I|L");
$reduction->setNature("A: Aliphatic");
$reduction->setReduction("a");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("F");
$reduction->setNature("F: Phenylalanine");
$reduction->setReduction("f");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("C|M");
$reduction->setNature("G: Sulfur");
$reduction->setReduction("c");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("G");
$reduction->setNature("G: Glycine");
$reduction->setReduction("g");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("S|T");
$reduction->setNature("S: Hydroxyl");
$reduction->setReduction("s");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("W");
$reduction->setNature("W: Tryptophan");
$reduction->setReduction("w");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("Y");
$reduction->setNature("Y: Tyrosine");
$reduction->setReduction("y");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("P");
$reduction->setNature("P: Proline");
$reduction->setReduction("p");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("D|E");
$reduction->setNature("A: Acidic");
$reduction->setReduction("d");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("N|Q");
$reduction->setNature("N: Amide");
$reduction->setReduction("n");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("11IMG");
$reduction->setLetters("AFCGSWYPDNH");
$reduction->setPattern("H|K|R");
$reduction->setNature("H: Basic");
$reduction->setReduction("h");
$reduction->setDescription("11 IMGT amino acid chemical characteristics alphabet");
$aReductions[] = $reduction;


/**
 * Murphy15
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("L|V|I|M");
$reduction->setNature("L: Large hydrophobic");
$reduction->setReduction("l");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("C");
$reduction->setNature("C");
$reduction->setReduction("c");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("A");
$reduction->setNature("A");
$reduction->setReduction("a");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("G");
$reduction->setNature("G");
$reduction->setReduction("g");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("S");
$reduction->setNature("S");
$reduction->setReduction("s");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("T");
$reduction->setNature("T");
$reduction->setReduction("t");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("P");
$reduction->setNature("P");
$reduction->setReduction("p");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("F|Y");
$reduction->setNature("F: Hydrophobic/aromatic sidechains");
$reduction->setReduction("f");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("W");
$reduction->setNature("W");
$reduction->setReduction("w");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("E");
$reduction->setNature("E");
$reduction->setReduction("e");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("D");
$reduction->setNature("D");
$reduction->setReduction("d");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("N");
$reduction->setNature("N");
$reduction->setReduction("n");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("Q");
$reduction->setNature("Q");
$reduction->setReduction("q");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("K|R");
$reduction->setNature("K: Long-chain positively charged");
$reduction->setReduction("k");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy15");
$reduction->setLetters("LCAGSTPFWEDNQKH");
$reduction->setPattern("H");
$reduction->setNature("H");
$reduction->setReduction("h");
$reduction->setDescription("Murphy et al, 2000; 15 letters alphabet");
$aReductions[] = $reduction;

/**
 * Murphy10
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy10");
$reduction->setLetters("LCAGSPFEKH");
$reduction->setPattern("L|V|I|M");
$reduction->setNature("L: Large hydrophobic");
$reduction->setReduction("l");
$reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy10");
$reduction->setLetters("LCAGSPFEKH");
$reduction->setPattern("C");
$reduction->setNature("C");
$reduction->setReduction("c");
$reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy10");
$reduction->setLetters("LCAGSPFEKH");
$reduction->setPattern("A");
$reduction->setNature("A");
$reduction->setReduction("a");
$reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy10");
$reduction->setLetters("LCAGSPFEKH");
$reduction->setPattern("G");
$reduction->setNature("G");
$reduction->setReduction("g");
$reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy10");
$reduction->setLetters("LCAGSPFEKH");
$reduction->setPattern("S|T");
$reduction->setNature("Polar");
$reduction->setReduction("s");
$reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy10");
$reduction->setLetters("LCAGSPFEKH");
$reduction->setPattern("P");
$reduction->setNature("P");
$reduction->setReduction("p");
$reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy10");
$reduction->setLetters("LCAGSPFEKH");
$reduction->setPattern("F|Y|W");
$reduction->setNature("Hydrophobic/aromatic sidechains");
$reduction->setReduction("f");
$reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy10");
$reduction->setLetters("LCAGSPFEKH");
$reduction->setPattern("E|D|N|Q");
$reduction->setNature("Charged / polar");
$reduction->setReduction("e");
$reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy10");
$reduction->setLetters("LCAGSPFEKH");
$reduction->setPattern("K|R");
$reduction->setNature("Long-chain positively charged");
$reduction->setReduction("k");
$reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy10");
$reduction->setLetters("LCAGSPFEKH");
$reduction->setPattern("H");
$reduction->setNature("H");
$reduction->setReduction("h");
$reduction->setDescription("Murphy et al, 2000; 10 letters alphabet");
$aReductions[] = $reduction;

/**
 * Murphy 4
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy4");
$reduction->setLetters("LAFE");
$reduction->setPattern("L|V|I|M|C");
$reduction->setNature("L: Hydrophobic");
$reduction->setReduction("l");
$reduction->setDescription("Murphy et al, 2000; 4 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy4");
$reduction->setLetters("LAFE");
$reduction->setPattern("A|G|S|T|P");
$reduction->setNature("A");
$reduction->setReduction("a");
$reduction->setDescription("Murphy et al, 2000; 4 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy4");
$reduction->setLetters("LAFE");
$reduction->setPattern("F|Y|W");
$reduction->setNature("F: Hydrophobic/aromatic sidechains");
$reduction->setReduction("f");
$reduction->setDescription("Murphy et al, 2000; 4 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy4");
$reduction->setLetters("LAFE");
$reduction->setPattern("E|D|N|Q|K|R|H");
$reduction->setNature("E");
$reduction->setReduction("e");
$reduction->setDescription("Murphy et al, 2000; 4 letters alphabet");
$aReductions[] = $reduction;


/**
 * Murphy 2
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy2");
$reduction->setLetters("PE");
$reduction->setPattern("L|V|I|M|C|A|G|S|T|P|F|Y|W");
$reduction->setNature("P: Hydrophobic");
$reduction->setReduction("p");
$reduction->setDescription("Murphy et al, 2000; 2 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Murphy2");
$reduction->setLetters("PE");
$reduction->setPattern("E|D|N|Q|K|R|H");
$reduction->setNature("E: Hydrophilic");
$reduction->setReduction("e");
$reduction->setDescription("Murphy et al, 2000; 2 letters alphabet");
$aReductions[] = $reduction;


/**
 * Wang 5
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang5");
$reduction->setLetters("IAGEK");
$reduction->setPattern("C|M|F|I|L|V|W|Y");
$reduction->setNature("I");
$reduction->setReduction("i");
$reduction->setDescription("Wang & Wang, 1999; 5 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang5");
$reduction->setLetters("IAGEK");
$reduction->setPattern("A|T|H");
$reduction->setNature("A");
$reduction->setReduction("a");
$reduction->setDescription("Wang & Wang, 1999; 5 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang5");
$reduction->setLetters("IAGEK");
$reduction->setPattern("G|P");
$reduction->setNature("G");
$reduction->setReduction("g");
$reduction->setDescription("Wang & Wang, 1999; 5 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang5");
$reduction->setLetters("IAGEK");
$reduction->setPattern("D|E");
$reduction->setNature("E");
$reduction->setReduction("e");
$reduction->setDescription("Wang & Wang, 1999; 5 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang5");
$reduction->setLetters("IAGEK");
$reduction->setPattern("S|N|Q|R|K");
$reduction->setNature("K");
$reduction->setReduction("k");
$reduction->setDescription("Wang & Wang, 1999; 5 letters alphabet");
$aReductions[] = $reduction;

/**
 * WANG 5V
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang5v");
$reduction->setLetters("ILAEK");
$reduction->setPattern("C|M|F|I");
$reduction->setNature("I");
$reduction->setReduction("i");
$reduction->setDescription("Wang & Wang, 1999; 5 letters variant alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang5v");
$reduction->setLetters("ILAEK");
$reduction->setPattern("L|V|W|Y");
$reduction->setNature("L");
$reduction->setReduction("l");
$reduction->setDescription("Wang & Wang, 1999; 5 letters variant alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang5v");
$reduction->setLetters("ILAEK");
$reduction->setPattern("A|T|G|S");
$reduction->setNature("A");
$reduction->setReduction("a");
$reduction->setDescription("Wang & Wang, 1999; 5 letters variant alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang5v");
$reduction->setLetters("ILAEK");
$reduction->setPattern("N|Q|D|E");
$reduction->setNature("E");
$reduction->setReduction("e");
$reduction->setDescription("Wang & Wang, 1999; 5 letters variant alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang5v");
$reduction->setLetters("ILAEK");
$reduction->setPattern("H|P|R|K");
$reduction->setNature("K");
$reduction->setReduction("k");
$reduction->setDescription("Wang & Wang, 1999; 5 letters variant alphabet");
$aReductions[] = $reduction;

/**
 * Wang3
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang3");
$reduction->setLetters("IAE");
$reduction->setPattern("C|M|F|I|L|V|W|Y");
$reduction->setNature("I");
$reduction->setReduction("i");
$reduction->setDescription("Wang & Wang, 1999; 3 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang3");
$reduction->setLetters("IAE");
$reduction->setPattern("A|T|H|G|P|R");
$reduction->setNature("A");
$reduction->setReduction("a");
$reduction->setDescription("Wang & Wang, 1999; 3 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang3");
$reduction->setLetters("IAE");
$reduction->setPattern("D|E|S|N|Q|K");
$reduction->setNature("E");
$reduction->setReduction("e");
$reduction->setDescription("Wang & Wang, 1999; 3 letters alphabet");
$aReductions[] = $reduction;

/**
 * Wang 2
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang2");
$reduction->setLetters("IA");
$reduction->setPattern("C|M|F|I|L|V|W|Y");
$reduction->setNature("I");
$reduction->setReduction("i");
$reduction->setDescription("Wang & Wang, 1999; 2 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Wang2");
$reduction->setLetters("IA");
$reduction->setPattern("A|T|H|G|P|R|D|E|S|N|Q|K");
$reduction->setNature("A");
$reduction->setReduction("a");
$reduction->setDescription("Wang & Wang, 1999; 2 letters alphabet");
$aReductions[] = $reduction;

/**
 * Li10
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li10");
$reduction->setLetters("CYLVGPSNEK");
$reduction->setPattern("C");
$reduction->setNature("C");
$reduction->setReduction("c");
$reduction->setDescription("Li et al, 2003; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li10");
$reduction->setLetters("CYLVGPSNEK");
$reduction->setPattern("F|Y|W");
$reduction->setNature("Y");
$reduction->setReduction("y");
$reduction->setDescription("Li et al, 2003; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li10");
$reduction->setLetters("CYLVGPSNEK");
$reduction->setPattern("M|L");
$reduction->setNature("L");
$reduction->setReduction("l");
$reduction->setDescription("Li et al, 2003; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li10");
$reduction->setLetters("CYLVGPSNEK");
$reduction->setPattern("I|V");
$reduction->setNature("V");
$reduction->setReduction("v");
$reduction->setDescription("Li et al, 2003; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li10");
$reduction->setLetters("CYLVGPSNEK");
$reduction->setPattern("G");
$reduction->setNature("G");
$reduction->setReduction("g");
$reduction->setDescription("Li et al, 2003; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li10");
$reduction->setLetters("CYLVGPSNEK");
$reduction->setPattern("P");
$reduction->setNature("P");
$reduction->setReduction("p");
$reduction->setDescription("Li et al, 2003; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li10");
$reduction->setLetters("CYLVGPSNEK");
$reduction->setPattern("A|T|S");
$reduction->setNature("S");
$reduction->setReduction("s");
$reduction->setDescription("Li et al, 2003; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li10");
$reduction->setLetters("CYLVGPSNEK");
$reduction->setPattern("N|H");
$reduction->setNature("N");
$reduction->setReduction("n");
$reduction->setDescription("Li et al, 2003; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li10");
$reduction->setLetters("CYLVGPSNEK");
$reduction->setPattern("Q|E|D");
$reduction->setNature("E");
$reduction->setReduction("e");
$reduction->setDescription("Li et al, 2003; 10 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li10");
$reduction->setLetters("CYLVGPSNEK");
$reduction->setPattern("R|K");
$reduction->setNature("K");
$reduction->setReduction("k");
$reduction->setDescription("Li et al, 2003; 10 letters alphabet");
$aReductions[] = $reduction;

/**
 * Li5
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li5");
$reduction->setLetters("YIGSE");
$reduction->setPattern("C|F|Y|W");
$reduction->setNature("Y");
$reduction->setReduction("y");
$reduction->setDescription("Li et al, 2003; 5 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li5");
$reduction->setLetters("YIGSE");
$reduction->setPattern("M|L|I|V");
$reduction->setNature("I");
$reduction->setReduction("i");
$reduction->setDescription("Li et al, 2003; 5 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li5");
$reduction->setLetters("YIGSE");
$reduction->setPattern("G");
$reduction->setNature("G");
$reduction->setReduction("g");
$reduction->setDescription("Li et al, 2003; 5 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li5");
$reduction->setLetters("YIGSE");
$reduction->setPattern("P|A|T|S");
$reduction->setNature("S");
$reduction->setReduction("s");
$reduction->setDescription("Li et al, 2003; 5 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li5");
$reduction->setLetters("YIGSE");
$reduction->setPattern("N|H|Q|E|D|R|K");
$reduction->setNature("E");
$reduction->setReduction("e");
$reduction->setDescription("Li et al, 2003; 5 letters alphabet");
$aReductions[] = $reduction;

/**
 * Li4
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li4");
$reduction->setLetters("YISE");
$reduction->setPattern("C|F|Y|W");
$reduction->setNature("Y");
$reduction->setReduction("y");
$reduction->setDescription("Li et al, 2003; 4 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li4");
$reduction->setLetters("YISE");
$reduction->setPattern("M|L|I|V");
$reduction->setNature("I");
$reduction->setReduction("i");
$reduction->setDescription("Li et al, 2003; 4 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li4");
$reduction->setLetters("YISE");
$reduction->setPattern("G|P|A|T|S");
$reduction->setNature("S");
$reduction->setReduction("s");
$reduction->setDescription("Li et al, 2003; 4 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li4");
$reduction->setLetters("YISE");
$reduction->setPattern("N|H|Q|E|D|R|K");
$reduction->setNature("E");
$reduction->setReduction("e");
$reduction->setDescription("Li et al, 2003; 4 letters alphabet");
$aReductions[] = $reduction;

/**
 * Li3
 */
$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li3");
$reduction->setLetters("ISE");
$reduction->setPattern("C|F|Y|W|M|L|I|V");
$reduction->setNature("I");
$reduction->setReduction("i");
$reduction->setDescription("Li et al, 2003; 3 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li3");
$reduction->setLetters("ISE");
$reduction->setPattern("G|P|A|T|S");
$reduction->setNature("S");
$reduction->setReduction("s");
$reduction->setDescription("Li et al, 2003; 3 letters alphabet");
$aReductions[] = $reduction;

$reduction = new ProteinReductionDTO();
$reduction->setAlphabet("Li3");
$reduction->setLetters("ISE");
$reduction->setPattern("N|H|Q|E|D|R|K");
$reduction->setNature("E");
$reduction->setReduction("e");
$reduction->setDescription("Li et al, 2003; 3 letters alphabet");
$aReductions[] = $reduction;