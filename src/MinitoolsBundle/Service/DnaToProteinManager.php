<?php
/**
 * DNA To Protein Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 24 february 2019
 * Last modified 24 february 2019
 */
namespace MinitoolsBundle\Service;

class DnaToProteinManager
{
    private $aAminos;
    private $aTriplets;
    private $aTripletsCombinations;

    /**
     * DnaToProteinManager constructor.
     * @param   array   $aAminos
     * @param   array   $aTriplets
     * @param   array   $aTripletsCombinations
     */
    public function __construct($aAminos, $aTriplets, $aTripletsCombinations)
    {
        $this->aAminos                  = $aAminos;
        $this->aTriplets                = $aTriplets;
        $this->aTripletsCombinations    = $aTripletsCombinations;
    }

    /**
     * Find ORFs in sequence
     * @param   array   $aFrames
     * @param   int     $iProtsize
     * @param   bool    $bOnlyCoding
     * @param   bool    $bTrimmed
     * @return  mixed
     */
    public function findORF($aFrames, $iProtsize, $bOnlyCoding, $bTrimmed)
    {
        foreach ($aFrames as $n => $sPeptideSequence) {
            $sPeptideSequence = strtolower($sPeptideSequence);
            $aOligo = preg_split('/\*/',$sPeptideSequence);
            foreach ($aOligo as $m => $val) {
                if (strlen($val) >= $iProtsize) {
                    if ($bTrimmed) {
                        $aOligo[$m] = substr($val,0,strpos($val,"m")).strtoupper(substr($val,strpos($val,"m")));
                    } else {
                        $aOligo[$m] = strtoupper($val);
                    }
                }
            }
            $sNewPeptideSequence = "";
            foreach ($aOligo as $m => $val) {
                if($m != 0){
                    $sNewPeptideSequence .= "*".$val;
                } else {
                    $sNewPeptideSequence .= $val;
                }
            }
            // to avoid showing no coding, remove them from output sequence
            if($bOnlyCoding) {
                $sNewPeptideSequence = preg_replace("/f|l|i|m|v|s|p|t|a|y|h|q|n|k|d|e|c|w|r|g|x]/","_",$sNewPeptideSequence);
            }
            $aFrames[$n] = $sNewPeptideSequence;
        }
        return $aFrames;
    }

    /**
     * @param $seq
     * @return string
     */
    public function revCompDNA($seq)
    {
        $seq = strtoupper($seq);
        $original =  array("(A)","(T)","(G)","(C)","(Y)","(R)","(W)","(S)","(K)","(M)","(D)","(V)","(H)","(B)");
        $complement = array("t","a","c","g","r","y","w","s","m","k","h","b","d","v");
        $seq = preg_replace($original, $complement, $seq);
        $seq = strtoupper($seq);
        return $seq;
    }

    /**
     * Translates a DNA Sequence to proteins
     * @param   string  $sSequence
     * @param   string  $sGeneticCode
     * @return  string
     */
    public function translateDNAToProtein($sSequence, $sGeneticCode)
    {
        $aAminoAcids = array("F","L","I","M","V","S","P","T","A","Y","*","H","Q","N","K","D","E","C","W","R","G","X");

        $triplets[1] = $this->aTriplets["standard"]; // Standard genetic code
        $triplets[2] = $this->aTriplets["vertebrate_mitochondrial"]; // Vertebrate Mitochondrial
        $triplets[3] = $this->aTriplets["yeast_mitochondrial"]; // Yeast Mitochondrial
        $triplets[4] = $this->aTriplets["mold_protozoan_coelenterate_mitochondrial"]; // Mold, Protozoan and Coelenterate Mitochondrial. Mycoplasma, Spiroplasma
        $triplets[5] = $this->aTriplets["invertebrate_mitochondrial"];// Invertebrate Mitochondrial
        $triplets[6] = $this->aTriplets["ciliate_dasycladacean_hexamita_nuclear"]; // Ciliate Nuclear; Dasycladacean Nuclear; Hexamita Nuclear
        $triplets[9] = $this->aTriplets["echinoderm_mitochondrial"]; // Echinoderm Mitochondrial
        $triplets[10] = $this->aTriplets["euplotid_nuclear"]; // Euplotid Nuclear
        $triplets[11] = $this->aTriplets["bacterial_plant_plastid"]; // Bacterial and Plant Plastid
        $triplets[12] = $this->aTriplets["alternative_yeast_nuclear"]; // Alternative Yeast Nuclear
        $triplets[13] = $this->aTriplets["ascidian_mitochondria"]; // Ascidian Mitochondria
        $triplets[14] = $this->aTriplets["flatworm_mitochondrial"]; // Flatworm Mitochondrial
        $triplets[15] = $this->aTriplets["blepharisma_macronuclear"]; // Blepharisma Macronuclear
        $triplets[16] = $this->aTriplets["chlorophycean_mitochondrial"]; // Chlorophycean Mitochondrial
        $triplets[21] = $this->aTriplets["trematode_mitochondrial"]; // Trematode Mitochondrial
        $triplets[22] = $this->aTriplets["scenedesmus_obliquus_mitochondrial"]; // Scenedesmus obliquus mitochondrial
        $triplets[23] = $this->aTriplets["thraustochytrium_mitochondrial_code"]; // Thraustochytrium mitochondrial code

        // place a space after each triplete in the sequence
        $temp = chunk_split($sSequence,3,' ');

        // replace triplets by corresponding amnoacid
        $sPeptide = preg_replace ($triplets[$sGeneticCode], $aAminoAcids, $temp);

        // return peptide sequence
        return $sPeptide;
    }

    /**
     * Translate DNA to protein (custom)
     * @param   string  $sSequence
     * @param   string  $sGeneticCode
     * @return  string
     */
    function translateDNAToProteinCustomcode($sSequence, $sGeneticCode)
    {
        $temp = chunk_split($sSequence,3,' '); // The sequence is chopped and @ is inserted after each triplete

        // each triplete replace by corresponding amnoacid
        foreach($this->aTripletsCombinations as $key => $aTriplete) {
            $temp = str_replace($aTriplete,substr($sGeneticCode, $key, 1)."  ",$temp);
        }

        // no matching triplets -> X
        $temp = preg_replace ("(\S\S\S )", "X  ", $temp);
        $temp = substr ($temp, 0, -2);

        $sProtein = preg_replace ("/ /","",$temp);
        return $sProtein;
    }

    /**
     * Shows triplets and Proteins aligned
     * @param   string $sSequence
     * @param   string $sRvSequence
     * @param   string $aFrame
     * @return  string
     */
    function showTranslationsAligned($sSequence, $sRvSequence, $aFrame)
    {
        $sResults = "";
        $aChunkedFrames = [];

        $scale = "         10        20        30        40        50        60        70        80        90         ";
        $barr = "         |         |         |         |         |         |         |         |         |          ";

        foreach ($aFrame as $n => $sPeptideSequence) {
            $aChunkedFrames[$n] = chunk_split($sPeptideSequence, 1, '  ');
        }

        $sResults .= "<table><tr><td nowrap><pre>\n";
        // Show translation of of sequence in 5'->3' direction
        $sResults .= "<b>Translation of requested code (5'->3')</b>\n\n  $scale\n$barr\n";
        $i = 0;

        while ($i < strlen($sSequence)) {
            $sResults .= substr($sSequence, $i, 100) . "  ";
            if ($i < strlen($sSequence) - $i) {
                $sResults .= $i + 100;
            }
            $sResults .= "\n";
            $sResults .= substr($aChunkedFrames[1], $i, 100) . "\n";
            $sResults .= substr(" " . $aChunkedFrames[2], $i, 100) . "\n";
            $sResults .= substr("  " . $aChunkedFrames[3], $i, 100) . "\n\n";
            $i += 100;
        }

        if ($aFrame[6]) {
            $sResults .= "<b>Translation of requested code (complementary DNA chain)</b>\n\n  $scale\n$barr\n";
            $i = 0;
            while ($i < strlen($sRvSequence)) {
                $sResults .= substr($sRvSequence, $i, 100) . "  ";
                if ($i < strlen($sSequence) - $i) {
                    $sResults .= $i + 100;
                }
                $sResults .= "\n";
                $sResults .= substr($aChunkedFrames[4], $i, 100) . "\n";
                $sResults .= substr(" " . $aChunkedFrames[5], $i, 100) . "\n";
                $sResults .= substr("  " . $aChunkedFrames[6], $i, 100) . "\n\n";
                $i += 100;
            }
        }
        $sResults .= "</pre></td></tr></table>";
        return $sResults;
    }
}
