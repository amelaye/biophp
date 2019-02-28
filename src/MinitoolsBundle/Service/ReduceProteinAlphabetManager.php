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
    public function __construct($protein_colors)
    {
        $this->protein_colors = $protein_colors;
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
                $this->getPh($sSequence);
                break;
            case 5:
                $this->getARCTD($sSequence);
                break;
            case 6:
                $this->getARPNTD($sSequence);
                break;
            case "3IMG":
                $this->getPNH($sSequence);
                break;
            case "5IMG":
                $this->getGCEMF($sSequence);
                break;
            case "11IMG":
                $this->getAFCGSWYPDNH($sSequence);
                break;
            case "Murphy15":
                $this->getLCAGSTPFWEDNQKH($sSequence);
                break;
            case "Murphy10":
                $this->getLCAGSPFEKH($sSequence);
                break;
            case "Murphy8":
                $this->getLASPFEKH($sSequence);
                break;
            case "Murphy4":
                $this->getLAFE($sSequence);
                break;
            case "Murphy2":
                $this->getPE($sSequence);
                break;
            case "Wang5":
                $this->getIAGEK($sSequence);
                break;
            case "Wang5v":
                $this->getILAEK($sSequence);
                break;
            case "Wang3":
                $this->getIAE($sSequence);
                break;
            case "Wang2":
                $this->getIA($sSequence);
                break;
            case "Li10":
                $this->getCYLVGPSNEK($sSequence);
                break;
            case "Li5":
                $this->getYIGSE($sSequence);
                break;
            case "Li4":
                $this->getYISE($sSequence);
                break;
            case "Li3":
                $this->getISE($sSequence);
                break;
        }
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


    /**
     * @param $sSequence
     */
    private function getPH(&$sSequence)
    {
        $aPattern = [
            "/A|G|T|S|N|Q|D|E|H|R|K|P/",    // Hydrophilic
            "/C|M|F|I|L|V|W|Y/"             // Hydrophobic
        ];
        $aReplacement = ["p", "h"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    /**
     * @param $sSequence
     */
    private function getARCTD(&$sSequence)
    {
        $aPattern = [
            "/I|V|L/",          // Aliphatic
            "/F|Y|W|H/",        // Aromatic
            "/K|R|D|E/",        // Charged
            "/G|A|C|S/",        // Tiny
            "/T|M|Q|N|P/"       // Diverse
        ];
        $aReplacement = ["a", "r", "c", "t", "d"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    /**
     * @param $sSequence
     */
    private function getARPNTD(&$sSequence)
    {
        $aPattern = [
            "/I|V|L/",      // Aliphatic
            "/F|Y|W|H/",    // Aromatic
            "/K|R/",        // Pos. charged
            "/D|E/",        // Neg. charged
            "/G|A|C|S/",    // Tiny
            "/T|M|Q|N|P/"   // Diverse
        ];
        $aReplacement = ["a", "r", "p", "n", "t", "d"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    /**
     * @param $sSequence
     */
    private function getPNH(&$sSequence)
    {
        $aPattern = [
            "/D|N|E|Q|K|R/",        // Hydrophilic
            "/G|T|S|Y|P|M/",        // Neutral
            "/I|V|L|F|C|M|A|W/"     // Hydrophobic
        ];
        $aReplacement = ["p", "n", "h"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    /**
     * @param $sSequence
     */
    private function getGCEMF(&$sSequence) // GCEMF (IMGT amino acid volume)
    {
        $aPattern = [
            "/G|A|S/",          // 60-90
            "/C|D|P|N|T/",      // 108-117
            "/E|V|Q|H/",        // 138-154
            "/M|I|L|K|R/",      // 162-174
            "/F|Y|W/"           // 189-228
        ];
        $aReplacement = ["g", "c", "e", "m", "f"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    /**
     * @param $sSequence
     */
    private function getAFCGSWYPDNH(&$sSequence)
    {
        $aPattern = [
            "/A|V|I|L/",    // Aliphatic
            "/F/",          // Phenylalanine
            "/C|M/",        // Sulfur
            "/G/",          // Glycine
            "/S|T/",        // Hydroxyl
            "/W/",          // Tryptophan
            "/Y/",          // Tyrosine
            "/P/",          // Proline
            "/D|E/",        // Acidic
            "/N|Q/",        // Amide
            "/H|K|R/"       // Basic
        ];
        $aReplacement = ["a","f","c","g","s","w","y","p","d","n","h"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    /**
     * @param $sSequence
     */
    private function getLCAGSTPFWEDNQKH(&$sSequence)
    {
        $aPattern = [
            "/L|V|I|M/", "/C/", "/A/", "/G/", "/S/", "/T/", "/P/",  // Large hydrophobic
            "/F|Y/", "/W/", "/E/", "/D/", "/N/", "/Q/",             // Hydrophobic/aromatic sidechains
            "/K|R/", "/H/"                                          // Long-chain positively charged
        ];
        $aReplacement = ["l", "c", "a", "g", "s", "t", "p", "f", "w", "e", "d", "n", "q", "k", "h"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    /**
     * @param $sSequence
     */
    private function getLCAGSPFEKH(&$sSequence)
    {
        $aPattern = [
            "/L|V|I|M/", "/C/", "/A/", "/G/",   // Large hydrophobic
            "/S|T/", "/P/",                     // Polar
            "/F|Y|W/",                          // Hydrophobic/aromatic sidechains
            "/E|D|N|Q/",                        // Charged / polar
            "/K|R/", "/H/"                      // Long-chain positively charged
        ];
        $aReplacement = ["l", "c", "a", "g", "s", "p", "f", "e", "k", "h"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    /**
     * @param $sSequence
     */
    private function getLASPFEKH(&$sSequence)
    {
        $aPattern = [
            "/L|V|I|M|C/", "/A|G/",     // Hydrophobic
            "/S|T/", "/P/",             // Polar
            "/F|Y|W/", "/E|D|N|Q/",     // Hydrophobic/aromatic sidechains
            "/K|R/", "/H/"              // Long-chain positively charged
        ];
        $aReplacement = ["l", "a", "s", "p", "f", "e", "k", "h"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    private function getLAFE(&$sSequence)
    {
        $aPattern = [
            "/L|V|I|M|C/", "/A|G|S|T|P/",   // Hydrophobic
            "/F|Y|W/", "/E|D|N|Q|K|R|H/"    // Hydrophobic/aromatic sidechains
        ];
        $aReplacement = ["l", "a", "f", "e"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    private function getPE(&$sSequence)
    {
        $aPattern = [
            "/L|V|I|M|C|A|G|S|T|P|F|Y|W/",  //Hydrophobic
            "/E|D|N|Q|K|R|H/"               //Hydrophilic
        ];
        $aReplacement = ["p", "e"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    private function getIAGEK(&$sSequence)
    {
        $aPattern = ["/C|M|F|I|L|V|W|Y/", "/A|T|H/", "/G|P/", "/D|E/", "/S|N|Q|R|K/"];
        $aReplacement = ["i", "a", "g", "e", "k"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    private function getILAEK(&$sSequence)
    {
        $aPattern = ["/C|M|F|I/", "/L|V|W|Y/", "/A|T|G|S/", "/N|Q|D|E/", "/H|P|R|K/"];
        $aReplacement = ["i", "l", "a", "e", "k"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    private function getIAE(&$sSequence)
    {
        $aPattern = ["/C|M|F|I|L|V|W|Y/", "/A|T|H|G|P|R/", "/D|E|S|N|Q|K/"];
        $aReplacement = ["i", "a", "e"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    private function getIA(&$sSequence)
    {
        $aPattern = ["/C|M|F|I|L|V|W|Y/", "/A|T|H|G|P|R|D|E|S|N|Q|K/"];
        $aReplacement = ["i", "a"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    private function getCYLVGPSNEK(&$sSequence)
    {
        $aPattern = ["/C/", "/F|Y|W/", "/M|L/", "/I|V/", "/G/", "/P/", "/A|T|S/", "/N|H/", "/Q|E|D/", "/R|K/"];
        $aReplacement = ["c", "y", "l", "v", "g", "p", "s", "n", "e", "k"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }


    private function getYIGSE(&$sSequence)
    {
        $aPattern = ["/C|F|Y|W/", "/M|L|I|V/", "/G/", "/P|A|T|S/", "/N|H|Q|E|D|R|K/"];
        $aReplacement = ["y", "i", "g", "s", "e"];

        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }

    private function getYISE(&$sSequence)
    {
        $aPattern = ["/C|F|Y|W/", "/M|L|I|V/", "/G|P|A|T|S/", "/N|H|Q|E|D|R|K/"];
        $aReplacement = ["y", "i", "s", "e"];

        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }

    private function getISE(&$sSequence)
    {
        $aPattern = ["/C|F|Y|W|M|L|I|V/", "/G|P|A|T|S/", "/N|H|Q|E|D|R|K/"];
        $aReplacement = ["i", "s", "e"];
        $sSequence = preg_replace($aPattern, $aReplacement, $sSequence);
        $sSequence = strtoupper($sSequence);
    }
}