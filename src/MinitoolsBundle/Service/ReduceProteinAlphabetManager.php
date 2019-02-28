<?php
/**
 * Reduce Protein Alphabet Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 27 february 2019 - RIP Pasha =^._.^= ∫
 * Last modified 28 february 2019
 */
namespace MinitoolsBundle\Service;

class ReduceProteinAlphabetManager
{
    private $protein_colors;
    private $aReductions;

    public function __construct($protein_colors, $aReductions)
    {
        $this->protein_colors   = $protein_colors;
        $this->aReductions      = $aReductions;
    }

    public function printReducedCodeInfo($type)
    {
        if ($type == 20) {
            $message = "<b>Complete alphabet";
        }
        if ($type == 2) {
            $message =  "<b>Two letters alphabet</b>";
            $message .= "\nAGTSNQDEHRKP => P: Hydrophilic\nCMFILVWY =>H: Hydrophobic";
        }
        if ($type == 5) {
            $message = "<b>Five letters alphabet: Chemical / structural properties</b>";
            $message .= "\nIVL   => A: Aliphatic\nFYWH  => R: Aromatic\nKRDE  => C: Charged\nGACS  => T: Tiny\nTMQNP => D: Diverse";
        }
        if ($type == 6) {
            $message =  "<b>Six letters alphabet: Chemical / structural properties #2</b>";
            $message .= "\nIVL   => A: Aliphatic\nFYWH  => R: Aromatic\nKR    => C: Pos. charged\nDE    => C: Neg. charged\nGACS  => T: Tiny\nTMQNP => D: Diverse";
        }
        if ($type == "3IMG") {
            $message =  "<b>3 IMGT amino acid hydropathy alphabet</b>";
            $message .= "\nIVLFCMAW => P: Hydrophilic\nGTSYPM => N: Neutral\nDNEQKR =>H: Hydrophobic";
        }
        if ($type == "5IMG") {
            $message = "<b>5 IMGT amino acid volume alphabet</b>";
            $message .= "\nGAS   => G: 60-90\nCDPNT => C: 108-117\nEVQH  => E: 138-154\nMILKR => M: 162-174\nFYW   => F: 189-228";
        }
        if ($type == "11IMG") {
            $message = "<b>11 IMGT amino acid chemical characteristics alphabet</b>";
            $message .= "\nAVIL => A: Aliphatic\nF    => F: Phenylalanine\nCM   => C: Sulfur\nG    => G: Glycine\nST   => S: Hydroxyl\nW    => W: Tryptophan\nY    => Y: Tyrosine\nP    => P: Proline\nDE   => A: Acidic\nNQ   => N: Amide\nHKR  => H: Basic";
        }
        if ($type == "Murphy15") {
            $message = "<b>Murphy et al, 2000; 15 letters alphabet</b>";
            $message .= "\nLVIM => L: Large hydrophobic\nC    => C\nA    => A\nG    => G\nS    => S\nT    => T\nP    => P\nFY   => F: Hydrophobic/aromatic sidechains\nW    => W\nE    => E\nD    => D\nN    => N\nQ    => Q\nKR   => K: Long-chain positively charged\nH    => H";
        }
        if ($type == "Murphy10") {
            $message = "<b>Murphy et al, 2000; 10 letters alphabet</b>";
            $message .= "\nLVIM => L: Large hydrophobic\nC    => C\nA    => A\nG    => G\nST   => S: Polar\nP    => P\nFYW  => F:Hydrophobic/aromatic sidechains\nEDNQ => E: Charged / polar\nKR   => K: Long-chain positively charged\nH    =>H";
        }
        if ($type == "Murphy8") {
            $message = "<b>Murphy et al, 2000; 8 letters alphabet</b>";
            $message .= "\nLVIMC => L: Hydrophobic\nAG    => A\nST    => S: Polar\nP     => P\nFYW   => F: Hydrophobic/aromatic sidechains\nEDNQ  => E\nKR    => K: Long-chain positively charged\nH     => H";
        }
        if ($type == "Murphy4") {
            $message = "<b>Murphy et al, 2000; 4 letters alphabet</b>";
            $message .= "\nLVIMC   => L: Hydrophobic\nAGSTP   => A\nFYW     => F: Hydrophobic/aromatic sidechains\nEDNQKRH =>E";
        }
        if ($type == "Murphy2") {
            $message = "<b>Murphy et al, 2000; 2 letters alphabet</b>";
            $message .= "\nLVIMCAGSTPFYW => P: Hydrophobic\nEDNQKRH       => E: Hydrophilic";
        }
        if ($type == "Wang5") {
            $message =  "<b>Wang & Wang, 1999; 5 letters alphabet</b>";
            $message .= "\nCMFILVWY => I\nATH      => A\nGP       => G\nDE       => E\nSNQRK    => K";
        }
        if ($type == "Wang5v") {
            $message = "<b>Wang & Wang, 1999; 5 letters variant alphabet</b>";
            $message .= "\nCMFI => I\nLVWY => L\nATGS => A\nNQDE => E\nHPRK => K";
        }
        if ($type == "Wang3") {
            $message = "<b>Wang & Wang, 1999; 3 letters alphabet</b>";
            $message .= "\nCMFILVWY => I\nATHGPR   => A\nDESNQK   => E";
        }
        if ($type == "Wang2") {
            $message = "<b>Wang & Wang, 1999; 2 letters alphabet</b>";
            $message .= "\nCMFILVWY     => I\nATHGPRDESNQK => A";
        }
        if ($type == "Li10") {
            $message = "<b>Li et al, 2003; 10 letters alphabet</b>";
            $message .= "\nC   => C\nFYW => Y\nML  => L\nIV  => V\nG   => G\nP   => P\nATS => S\nNH  => N\nQED => E\nRK  => K";
        }
        if ($type == "Li5") {                                       // YIGSE
            $message = "<b>Li et al, 2003; 5 letters alphabet</b>";
            $message .= "\nCFYW    => Y\nMLIV    => I\nG       => G\nPATS    => S\nNHQEDRK => E";
        }
        if ($type == "Li4") {                                        // YISE
            $message = "<b>Li et al, 2003; 4 letters alphabet</b>";
            $message .= "\nCFYW    => Y\nMLIV    => I\nGPATS   => S\nNHQEDRK => E";
        }
        if ($type == "Li3") {                                       // ISE
            $message = "<b>Li et al, 2003; 3 letters alphabet</b>";
            $message .= "\nCFYWMLIV => I\nGPATS    => S\nNHQEDRK  => E";
        }
    }


    /**
     * Get colored html code for $seq by using the $seq2 (the reduced sequence)
     * as a reference, and according to the $type of reduction selected
     * returns an html code
     * @param $seq
     * @param $seq2
     * @param $type
     * @return string
     */
    public function color($seq, $seq2, $type)
    {
        $letters_array = $this->protein_colors;

        $new_seq = "";

        for($i = 0; $i < strlen($seq); $i ++) {
            $letter_seq = substr($seq,$i,1);
            $letter_seq2 = substr($seq2,$i,1);
            if ($letters_array[$type][$letter_seq2] != "") {
                $new_seq .= "<font color=".strtolower($letters_array[$type][$letter_seq2]).">$letter_seq</font>";
            }else{
                $new_seq .= $letter_seq;
            }
        }

        return $new_seq;
    }



    /**
     * Reduce alphabet for $seq by using the predefined $type type of reduction
     * returns a reduced sequence
     * @param   string  $sSequence
     * @param   void    $type
     * @return  string
     */
    public function reduceAlphabet($sSequence, $type)
    {
        switch($type) {
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
    }


    /**
     * Removes non coding characters from $seq
     * returns the filtered sequence
     * @param $seq
     * @return string
     */
    public function removeNonCodingProt($seq)
    {
        // change the sequence to upper case
        $seq = strtoupper($seq);
        // remove non-coding characters([^ARNDCEQGHILKMFPSTWYVX\*])
        $seq = preg_replace ("([^ARNDCEQGHILKMFPSTWYVX\*])", "", $seq);
        return $seq;
    }


    /**
     * Reduce the alphabet for $seq by using the user defined personalized alphabet
     * returns the reduced sequence
     * @param $seq
     * @param $custom_alphabet
     * @return string
     */
    public function reduceAlphabetCustom($seq, $custom_alphabet)
    {
        $custom_alphabet = strtolower($custom_alphabet);
        // array with reduced code
        $a = preg_split("//",$custom_alphabet,-1,PREG_SPLIT_NO_EMPTY);
        // array with aminoacids
        $b = preg_split("//","ARNDCEQGHILKMFPSTWYV",-1,PREG_SPLIT_NO_EMPTY);

        foreach($a as $key=> $val) {
            // replace aminoacids by reduced codes
            $seq = preg_replace("/".$b[$key]."/", $val, $seq);
        }
        $seq = strtoupper($seq);
        return $seq;
    }


    /**
     * Get colored html code for $seq by using the $seq2 (the reduced sequence)
     * as a reference, and according to the personalized alphabet included in the form
     * returns an html code
     * @param $seq
     * @param $seq2
     * @param $custom_alphabet
     * @return string
     */
    public function colorCustom($seq, $seq2, $custom_alphabet)
    {
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
            if ($letters[$letter_seq2] != ""){
                $new_seq .= "<font color=".strtolower($letters[$letter_seq2]).">$letter_seq</font>";
            }else{
                $new_seq .= $letter_seq;
            }
        }

        return $new_seq;
    }
}