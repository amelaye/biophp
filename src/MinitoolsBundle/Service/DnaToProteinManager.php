<?php
/**
 * DNA To Protein Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 24 february 2019
 * Last modified 3 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use MinitoolsBundle\Entity\DnaToProtein;

class DnaToProteinManager
{
    private $aAminos;
    private $aTriplets;
    private $aTripletsCombinations;
    private $sRvSequence;

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
     * Set reverted sequence
     * @param   string      $sSequence
     * @throws  \Exception
     */
    public function getRvSequence($sSequence)
    {
        $this->sRvSequence = $this->revCompDNA($sSequence);
    }

    /**
     * Sets the bar + scale
     * @return string
     */
    public function getScaleAndBar()
    {
        $sScale = "         10        20        30        40        50        60        70        80        90         \r";
        $aBar   = "         |         |         |         |         |         |         |         |         |          ";
        return "$sScale\n$aBar";
    }

    /**
     * @param   DnaToProtein $oDnaToProtein
     * @param   string      $sSequence
     * @param   string      $sMycode
     * @return  array
     * @throws \Exception
     */
    public function customTreatment(DnaToProtein $oDnaToProtein, $sSequence, $sMycode)
    {
        try {
            $aFrames = [];
            // Translate in  5-3 direction
            $aFrames[1] = $this->translateDNAToProteinCustomcode(substr($sSequence, 0, floor(strlen($sSequence)/3)*3), $sMycode);

            if ($oDnaToProtein->getFrames() > 1) {
                $aFrames[2] = $this->translateDNAToProteinCustomcode(substr($sSequence, 1,floor((strlen($sSequence)-1)/3)*3),$sMycode);
                $aFrames[3] = $this->translateDNAToProteinCustomcode(substr($sSequence, 2,floor((strlen($sSequence)-2)/3)*3),$sMycode);
            }
            // Translate the complementary sequence
            if ($oDnaToProtein->getFrames() > 3) {
                // Get complementary
                $this->sRvSequence = $this->revCompDNA($sSequence);
                $aFrames[4] = $this->translateDNAToProteinCustomcode(substr($this->sRvSequence, 0, floor(strlen($this->sRvSequence)/3)*3),$sMycode);
                $aFrames[5] = $this->translateDNAToProteinCustomcode(substr($this->sRvSequence, 1,floor((strlen($this->sRvSequence)-1)/3)*3),$sMycode);
                $aFrames[6] = $this->translateDNAToProteinCustomcode(substr($this->sRvSequence, 2,floor((strlen($this->sRvSequence)-2)/3)*3),$sMycode);
            }
            return $aFrames;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Treatment when a specie has been chosen
     * @param   DnaToProtein    $oDnaToProtein
     * @param   string          $sSequence
     * @return  array
     * @throws  \Exception
     */
    public function definedTreatment(DnaToProtein $oDnaToProtein, $sSequence)
    {
        try {
            // Translate in 5-3 direction
            $aFrames[1] = $this->translateDNAToProtein(substr($sSequence, 0, floor(strlen($sSequence)/3)*3),$oDnaToProtein->getGeneticCode());
            if ($oDnaToProtein->getFrames() > 1){
                $aFrames[2] = $this->translateDNAToProtein(substr($sSequence, 1,floor((strlen($sSequence)-1)/3)*3),$oDnaToProtein->getGeneticCode());
                $aFrames[3] = $this->translateDNAToProtein(substr($sSequence, 2,floor((strlen($sSequence)-2)/3)*3),$oDnaToProtein->getGeneticCode());
            }
            // Translate the complementary sequence
            if ($oDnaToProtein->getFrames() > 3){
                // Get complementary
                $this->sRvSequence = $this->revCompDNA($sSequence);
                //calculate frames 4-6
                $aFrames[4] = $this->translateDNAToProtein(substr($this->sRvSequence, 0,floor(strlen($this->sRvSequence)/3)*3),$oDnaToProtein->getGeneticCode());
                $aFrames[5] = $this->translateDNAToProtein(substr($this->sRvSequence, 1,floor((strlen($this->sRvSequence)-1)/3)*3),$oDnaToProtein->getGeneticCode());
                $aFrames[6] = $this->translateDNAToProtein(substr($this->sRvSequence, 2,floor((strlen($this->sRvSequence)-2)/3)*3),$oDnaToProtein->getGeneticCode());
            }
            return $aFrames;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Find ORFs in sequence
     * @param   array       $aFrames
     * @param   int         $iProtsize
     * @param   bool        $bOnlyCoding
     * @param   bool        $bTrimmed
     * @return  array
     * @throws  \Exception
     */
    public function findORF($aFrames, $iProtsize, $bOnlyCoding, $bTrimmed)
    {
        try {
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
                // To avoid showing no coding, remove them from output sequence
                if($bOnlyCoding) {
                    $sNewPeptideSequence = preg_replace("/f|l|i|m|v|s|p|t|a|y|h|q|n|k|d|e|c|w|r|g|x]/","_",$sNewPeptideSequence);
                }
                $aFrames[$n] = $sNewPeptideSequence;
            }
            return $aFrames;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * @param   string          $sSequence
     * @return  string
     * @throws  \Exception
     */
    public function revCompDNA($sSequence)
    {
        try {
            $sSequence = strtoupper($sSequence);
            $original   = ["(A)","(T)","(G)","(C)","(Y)","(R)","(W)","(S)","(K)","(M)","(D)","(V)","(H)","(B)"];
            $complement = ["t","a","c","g","r","y","w","s","m","k","h","b","d","v"];
            $sSequence = preg_replace($original, $complement, $sSequence);
            $sSequence = strtoupper($sSequence);
            return $sSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Translates a DNA Sequence to proteins
     * @param   string          $sSequence
     * @param   string          $sGeneticCode
     * @return  string
     * @throws  \Exception
     */
    public function translateDNAToProtein($sSequence, $sGeneticCode)
    {
        try {
            $aAminoAcids = ["F","L","I","M","V","S","P","T","A","Y","*","H","Q","N","K","D","E","C","W","R","G","X"];

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
            $sPeptide = preg_replace($triplets[$sGeneticCode], $aAminoAcids, $temp);
            // return peptide sequence
            return $sPeptide;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Translate DNA to protein (custom)
     * @param   string          $sSequence
     * @param   string          $sGeneticCode
     * @return  string
     * @throws  \Exception
     */
    public function translateDNAToProteinCustomcode($sSequence, $sGeneticCode)
    {
        try {
            $temp = chunk_split($sSequence,3,' '); // The sequence is chopped and @ is inserted after each triplete

            // each triplete replace by corresponding amnoacid
            foreach ($this->aTripletsCombinations as $key => $aTriplete) {
                $temp = str_replace($aTriplete,substr($sGeneticCode, $key, 1)."  ",$temp);
            }

            // no matching triplets -> X
            $temp = preg_replace("(\S\S\S )", "X  ", $temp);
            $temp = substr($temp, 0, -2);

            $sProtein = preg_replace("/ /","",$temp);
            return $sProtein;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Shows triplets and Proteins aligned (5'->3')
     * @param   string      $sSequence
     * @param   array       $aFrame
     * @return  string
     * @throws  \Exception
     */
    public function showTranslationsAligned($sSequence, $aFrame)
    {
        try {
            $aResults = [];
            $aChunkedFrames = [];

            foreach ($aFrame as $n => $sPeptideSequence) {
                $aChunkedFrames[$n] = chunk_split($sPeptideSequence, 1, '  ');
            }

            $i = 0;

            while ($i < strlen($sSequence)) {
                $aResults[] = substr($sSequence, $i, 100) . "  ";
                if ($i < strlen($sSequence) - $i) {
                    $aResults[] = $i + 100;
                }
                $aResults[] = "\n";
                $aResults[] = substr($aChunkedFrames[1], $i, 100) . "\n";
                if (isset($aChunkedFrames[2])) {
                    $aResults[] = substr(" " . $aChunkedFrames[2], $i, 100) . "\n";
                }
                if (isset( $aChunkedFrames[3])) {
                    $aResults[] = substr("  " . $aChunkedFrames[3], $i, 100) . "\n\n";
                }

                $i += 100;
            }

            $sResults = implode('', $aResults);
            return $sResults;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Shows triplets and Proteins aligned (complementary DNA chain)
     * @param   array    $aFrame
     * @return  string
     * @throws  \Exception
     */
    public function showTranslationsAlignedComplementary($aFrame)
    {
        try {
            $sResults = "";
            $aChunkedFrames = [];

            foreach ($aFrame as $n => $sPeptideSequence) {
                $aChunkedFrames[$n] = chunk_split($sPeptideSequence, 1, '  ');
            }

            if (isset($aFrame[6])) {
                $i = 0;
                while ($i < strlen($this->sRvSequence)) {
                    $sResults .= substr($this->sRvSequence, $i, 100) . "  ";
                    if ($i < strlen($this->sRvSequence) - $i) {
                        $sResults .= $i + 100;
                    }
                    $sResults .= "\n";
                    $sResults .= substr($aChunkedFrames[4], $i, 100) . "\n";
                    $sResults .= substr(" " . $aChunkedFrames[5], $i, 100) . "\n";
                    $sResults .= substr("  " . $aChunkedFrames[6], $i, 100) . "\n\n";
                    $i += 100;
                }
            }

            return $sResults;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
