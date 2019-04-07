<?php
/**
 * Class ReduceProteinAlphabetManager
 * Inspired by BioPHP's project biophp.org
 * Created 27 february 2019 - RIP Pasha =^._.^= ∫
 * Last modified 7 april 2019
 */
namespace MinitoolsBundle\Service;

/**
 * Reduce Protein Alphabet Functions
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class ReduceProteinAlphabetManager
{
    /**
     * @var array
     */
    private $protein_colors;

    /**
     * @var array
     */
    private $aReductions;

    /**
     * ReduceProteinAlphabetManager constructor.
     * @param       array   $protein_colors
     * @param       array   $aReductions
     */
    public function __construct($protein_colors, $aReductions)
    {
        $this->protein_colors   = $protein_colors;
        $this->aReductions      = $aReductions;
    }

    /**
     * Returns informations about type
     * @param       string          $sType
     * @return      array
     * @throws      \Exception
     */
    public function printReducedCodeInfo($sType)
    {
        try {
            $reducedCodesInfos[20] = [
                "Description" => "Complete alphabet",
                "Elements"    => ["-"]
            ];

            $reducedCodesInfos[2] = [
                "Description" => "Two letters alphabet",
                "Elements"    => ["AGTSNQDEHRKP" => "P: Hydrophilic", "CMFILVWY" => "H: Hydrophobic"]
            ];

            $reducedCodesInfos[5] = [
                "Description" => "Five letters alphabet: Chemical / structural properties",
                "Elements"    => [
                    "IVL"   => "A: Aliphatic",
                    "FYWH"  => "R: Aromatic",
                    "KRDE"  => "C: Charged",
                    "GACS"  => "T: Tiny",
                    "TMQNP" => "D: Diverse"
                ]
            ];

            $reducedCodesInfos[6] = [
                "Description" => "Six letters alphabet: Chemical / structural properties #2",
                "Elements"    => [
                    "IVL"   => "A: Aliphatic",
                    "FYWH"  => "R: Aromatic",
                    "KR"    => "C: Pos. charged",
                    "DE"    => "C: Neg. charged",
                    "GACS"  => "T: Tiny",
                    "TMQNP" => "D: Diverse"
                ]
            ];

            $reducedCodesInfos["3IMG"] = [
                "Description"   => "3 IMGT amino acid hydropathy alphabet",
                "Elements"      => [
                    "IVLFCMAW"  => "P: Hydrophilic",
                    "GTSYPM"    => "N: Neutral",
                    "DNEQKR"    => "H: Hydrophobic"
                ]
            ];

            $reducedCodesInfos["5IMG"] = [
                "Description" => "5 IMGT amino acid volume alphabet",
                "Elements"    => [
                    "GAS"   => "G: 60-90",
                    "CDPNT" => "C: 108-117",
                    "EVQH"  => "E: 138-154",
                    "MILKR" => "M: 162-174",
                    "FYW"   => "F: 189-228"
                ]
            ];

            $reducedCodesInfos["11IMG"] = [
                "Description" => "11 IMGT amino acid chemical characteristics alphabet",
                "Elements"    => [
                    "AVIL"  => "A: Aliphatic",
                    "F"     => "F: Phenylalanine",
                    "CM"    => "C: Sulfur",
                    "G"     => "G: Glycine",
                    "ST"    => "S: Hydroxyl",
                    "W"     => "W: Tryptophan",
                    "Y"     => "Y: Tyrosine",
                    "P"     => "P: Proline",
                    "DE"    => "A: Acidic",
                    "NQ"    => "N: Amide",
                    "HKR"   => "H: Basic"
                ]
            ];

            $reducedCodesInfos["Murphy15"] = [
                "Description" => "Murphy et al, 2000; 15 letters alphabet",
                "Elements"    => [
                    "LVIM"  => "L: Large hydrophobic",
                    "C"     => "C",
                    "A"     => "A",
                    "G"     => "G",
                    "S"     => "S",
                    "T"     => "T",
                    "P"     => "P",
                    "FY"    => "F: Hydrophobic/aromatic sidechains",
                    "W"     => "W",
                    "E"     => "E",
                    "D"     => "D",
                    "N"     => "N",
                    "Q"     => "Q",
                    "KR"    => "K: Long-chain positively charged",
                    "H"     => "H"
                ]
            ];

            $reducedCodesInfos["Murphy10"] = [
                "Description" => "Murphy et al, 2000; 10 letters alphabet",
                "Elements"    => [
                    "LVIM" => "L: Large hydrophobic",
                    "C"    => "C",
                    "A"    => "A",
                    "G"    => "G",
                    "ST"   => "S: Polar",
                    "P"    => "P",
                    "FYW"  => "F:Hydrophobic/aromatic sidechains",
                    "EDNQ" => "E: Charged / polar",
                    "KR"   => "K: Long-chain positively charged",
                    "H"    => "H"
                ]
            ];

            $reducedCodesInfos["Murphy8"] = [
                "Description" => "Murphy et al, 2000; 8 letters alphabet",
                "Elements"    => [
                    "LVIMC" => "L: Hydrophobic",
                    "AG"    => "A",
                    "ST"    => "S: Polar",
                    "P"     => "P",
                    "FYW"   => "F: Hydrophobic/aromatic sidechains",
                    "EDNQ"  => "E",
                    "KR"    => "K: Long-chain positively charged",
                    "H"     => "H"
                ]
            ];

            $reducedCodesInfos["Murphy4"] = [
                "Description" => "Murphy et al, 2000; 4 letters alphabet",
                "Elements"    => [
                    "LVIMC"   => "L: Hydrophobic",
                    "AGSTP"   => "A",
                    "FYW"     => "F: Hydrophobic/aromatic sidechains",
                    "EDNQKRH" => "E"
                ]
            ];

            $reducedCodesInfos["Murphy2"] = [
                "Description" => "Murphy et al, 2000; 2 letters alphabet",
                "Elements"    => [
                    "LVIMCAGSTPFYW" => "P: Hydrophobic",
                    "EDNQKRH"       => "E: Hydrophilic"
                ]
            ];

            $reducedCodesInfos["Wang5"] = [
                "Description" => "Wang & Wang, 1999; 5 letters alphabet",
                "Elements"    => [
                    "CMFILVWY" => "I",
                    "ATH"      => "A",
                    "GP"       => "G",
                    "DE"       => "E",
                    "SNQRK"    => "K"
                ]
            ];

            $reducedCodesInfos["Wang5v"] = [
                "Description" => "Wang & Wang, 1999; 5 letters variant alphabet",
                "Elements"    => [
                    "CMFI" => "I",
                    "LVWY" => "L",
                    "ATGS" => "A",
                    "NQDE" => "E",
                    "HPRK" => "K"
                ]
            ];

            $reducedCodesInfos["Wang3"] = [
                "Description" => "Wang & Wang, 1999; 3 letters alphabet",
                "Elements"    => [
                    "CMFILVWY" => "I",
                    "ATHGPR"   => "A",
                    "DESNQK"   => "E"
                ]
            ];

            $reducedCodesInfos["Wang2"] = [
                "Description" => "Wang & Wang, 1999; 2 letters alphabet",
                "Elements"    => [
                    "CMFILVWY"     => "I",
                    "ATHGPRDESNQK" => "A"
                ]
            ];

            $reducedCodesInfos["Li10"] = [
                "Description" => "Li et al, 2003; 10 letters alphabet",
                "Elements"    => [
                    "C"   => "C",
                    "FYW" => "Y",
                    "ML"  => "L",
                    "IV"  => "V",
                    "G"   => "G",
                    "P"   => "P",
                    "ATS" => "S",
                    "NH"  => "N",
                    "QED" => "E",
                    "RK"  => "K"
                ]
            ];

            $reducedCodesInfos["Li5"] = [
                "Description" => "Li et al, 2003; 5 letters alphabet",
                "Elements"    => [
                    "CFYW"    => "Y",
                    "MLIV"    => "I",
                    "G"       => "G",
                    "PATS"    => "S",
                    "NHQEDRK" => "E"
                ]
            ];

            $reducedCodesInfos["Li4"] = [
                "Description" => "Li et al, 2003; 4 letters alphabet",
                "Elements"    => [
                    "CFYW"    => "Y",
                    "MLIV"    => "I",
                    "GPATS"   => "S",
                    "NHQEDRK" => "E"
                ]
            ];

            $reducedCodesInfos["Li3"] = [
                "Description" => "Li et al, 2003; 3 letters alphabet",
                "Elements"    => [
                    "CFYWMLIV" => "I",
                    "GPATS"    => "S",
                    "NHQEDRK"  => "E"
                ]
            ];

            return $reducedCodesInfos[$sType];
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Get colored html code for $seq by using the $seq2 (the reduced sequence)
     * as a reference, and according to the $type of reduction selected
     * returns an html code
     * @param       string          $sSequence
     * @param       string          $sSequence2
     * @param       string          $sType
     * @return      string
     * @throws      \Exception
     */
    public function color($sSequence, $sSequence2, $sType)
    {
        try {
            $letters_array = $this->protein_colors;
            $newSeq = "";

            for($i = 0; $i < strlen($sSequence); $i ++) {
                $sLetterSeq = substr($sSequence,$i,1);
                $sLetterSeq2 = substr($sSequence2,$i,1);

                if (isset($letters_array[$sType][$sLetterSeq2]) && $letters_array[$sType][$sLetterSeq2] != "") {
                    $newSeq .= "<font color=".strtolower($letters_array[$sType][$sLetterSeq]).">$sLetterSeq</font>";
                } else {
                    $newSeq .= $sLetterSeq;
                }
            }

            return $newSeq;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Reduce alphabet for $seq by using the predefined $type type of reduction
     * returns a reduced sequence
     * @param   string $sSequence
     * @param   string $sType
     * @return  string
     * @throws  \Exception
     */
    public function reduceAlphabet($sSequence, $sType)
    {
        try {
            switch($sType) {
                case 2:
                    $aPattern       = $this->aReductions["PH"]["pattern"];
                    $aReplacement   = $this->aReductions["PH"]["reduction"];
                    break;
                case 5:
                    $aPattern       = $this->aReductions["ARCTD"]["pattern"];
                    $aReplacement   = $this->aReductions["ARCTD"]["reduction"];
                    break;
                case 6:
                    $aPattern       = $this->aReductions["ARPNTD"]["pattern"];
                    $aReplacement   = $this->aReductions["ARPNTD"]["reduction"];
                    break;
                case "3IMG":
                    $aPattern       = $this->aReductions["PNH"]["pattern"];
                    $aReplacement   = $this->aReductions["PNH"]["reduction"];
                    break;
                case "5IMG": // GCEMF (IMGT amino acid volume)
                    $aPattern       = $this->aReductions["GCEMF"]["pattern"];
                    $aReplacement   = $this->aReductions["GCEMF"]["reduction"];
                    break;
                case "11IMG":
                    $aPattern       = $this->aReductions["AFCGSWYPDNH"]["pattern"];
                    $aReplacement   = $this->aReductions["AFCGSWYPDNH"]["reduction"];
                    break;
                case "Murphy15":
                    $aPattern       = $this->aReductions["LCAGSTPFWEDNQKH"]["pattern"];
                    $aReplacement   = $this->aReductions["LCAGSTPFWEDNQKH"]["reduction"];
                    break;
                case "Murphy10":
                    $aPattern       = $this->aReductions["LCAGSPFEKH"]["pattern"];
                    $aReplacement   = $this->aReductions["LCAGSPFEKH"]["reduction"];
                    break;
                case "Murphy8":
                    $aPattern       = $this->aReductions["LASPFEKH"]["pattern"];
                    $aReplacement   = $this->aReductions["LASPFEKH"]["replacement"];
                    break;
                case "Murphy4":
                    $aPattern       = $this->aReductions["LAFE"]["pattern"];
                    $aReplacement   = $this->aReductions["LAFE"]["pattern"];
                    break;
                case "Murphy2":
                    $aPattern       = $this->aReductions["PE"]["pattern"];
                    $aReplacement   = $this->aReductions["PE"]["reduction"];
                    break;
                case "Wang5":
                    $aPattern       = $this->aReductions["IAGEK"]["pattern"];
                    $aReplacement   = $this->aReductions["IAGEK"]["reduction"];
                    break;
                case "Wang5v":
                    $aPattern       = $this->aReductions["ILAEK"]["pattern"];
                    $aReplacement   = $this->aReductions["ILAEK"]["reduction"];
                    break;
                case "Wang3":
                    $aPattern       = $this->aReductions["IAE"]["pattern"];
                    $aReplacement   = $this->aReductions["IAE"]["reduction"];
                    break;
                case "Wang2":
                    $aPattern       = $this->aReductions["IA"]["pattern"];
                    $aReplacement   = $this->aReductions["IA"]["reduction"];
                    break;
                case "Li10":
                    $aPattern       = $this->aReductions["CYLVGPSNEK"]["pattern"];
                    $aReplacement   = $this->aReductions["CYLVGPSNEK"]["reduction"];
                    break;
                case "Li5":
                    $aPattern       = $this->aReductions["YIGSE"]["pattern"];
                    $aReplacement   = $this->aReductions["YIGSE"]["reduction"];
                    break;
                case "Li4":
                    $aPattern       = $this->aReductions["YISE"]["pattern"];
                    $aReplacement   = $this->aReductions["YISE"]["reduction"];
                    break;
                case "Li3":
                    $aPattern       = $this->aReductions["ISE"]["pattern"];
                    $aReplacement   = $this->aReductions["ISE"]["reduction"];
                    break;
                default:
                    $aPattern       = [];
                    $aReplacement   = [];
            }
            $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
            $sSequence = strtoupper($sSequence);
            return $sSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Reduce the alphabet for $seq by using the user defined personalized alphabet
     * returns the reduced sequence
     * @param       string          $sSequence
     * @param       string          $sCustomAlphabet
     * @return      string
     * @throws      \Exception
     */
    public function reduceAlphabetCustom($sSequence, $sCustomAlphabet)
    {
        try {
            $sCustomAlphabet = strtolower($sCustomAlphabet);
            // array with reduced code
            $a = preg_split("//",$sCustomAlphabet,-1,PREG_SPLIT_NO_EMPTY);
            // array with aminoacids
            $b = preg_split("//","ARNDCEQGHILKMFPSTWYV",-1,PREG_SPLIT_NO_EMPTY);

            foreach($a as $key=> $val) {
                // replace aminoacids by reduced codes
                $sSequence = preg_replace("/".$b[$key]."/", $val, $sSequence);
            }
            $sSequence = strtoupper($sSequence);
            return $sSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Get colored html code for $seq by using the $seq2 (the reduced sequence)
     * as a reference, and according to the personalized alphabet included in the form
     * returns an html code
     * @param       string          $seq
     * @param       string          $seq2
     * @param       string          $custom_alphabet
     * @return      string
     * @throws      \Exception
     */
    public function colorCustom($seq, $seq2, $custom_alphabet)
    {
        try {
            // get array with letters
            $a = preg_split("//",$custom_alphabet,-1,PREG_SPLIT_NO_EMPTY);
            $a = array_unique($a);

            // define generic color
            //   the assigment order is the one shown in the list
            //   When few colors are needed, the first ones in the list are used
            $generic_colors = array(
                0 => "FF0000",
                1 => "00FF00",
                2 => "0000FF",
                3 => "FFFF00",
                4 => "FF00FF",
                5 => "00FFFF",
                6 => "FF8888",
                7 => "88FF88",
                8 => "8888FF",
                9 => "FFFF88",
                10 => "FF88FF",
                11 => "88FFFF",
                12 => "FF3366",
                13 => "33FF66",
                14 => "3366FF",
                15 => "FF6633",
                16 => "66FF33",
                17 => "6633FF",
                18 => "880000",
                19 => "008800"
            );

            foreach($a as $key => $val) {
                $letters[$val] = $generic_colors[$key];
            }

            $new_seq = "";
            for($i = 0; $i < strlen($seq); $i++) {
                $letter_seq = substr($seq,$i,1);
                $letter_seq2 = substr($seq2,$i,1);
                if (isset($letters[$letter_seq2]) && $letters[$letter_seq2] != ""){
                    $new_seq .= "<font color=".strtolower($letters[$letter_seq2]).">$letter_seq</font>";
                }else{
                    $new_seq .= $letter_seq;
                }
            }

            return $new_seq;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}