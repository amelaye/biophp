<?php
/**
 * DNA To Protein Functions
 * Inspired by BioPHP's project biophp.org
 * Created 24 february 2019
 * Last modified 23 july 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;


use AppBundle\Bioapi\Bioapi;
use AppBundle\Traits\SequenceTrait;

/**
 * Class DnaToProteinManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @todo : beaucoup de fonctions de format !
 */
class DnaToProteinManager
{
    use SequenceTrait;

    /**
     * @var array
     */
    private $aAminos;

    /**
     * @var array
     */
    private $aTriplets;

    /**
     * @var array
     */
    private $aTripletsCombinations;

    /**
     * @var string
     */
    private $sRvSequence;

    /**
     * DnaToProteinManager constructor.
     * @param   Bioapi   $bioapi
     */
    public function __construct(Bioapi $bioapi)
    {
        $this->aAminos                  = $bioapi->getAminosOnlyLetters();
        $this->aTriplets                = $bioapi->getTripletsGroups();
        $this->aTripletsCombinations    = $bioapi->getTripletsList();
    }

    /**
     * Splits the array og aminos
     * @param $aAminoAcidCodes
     * @param $aAminoAcidCodesLeft
     * @param $aAminoAcidCodesRight
     */
    public function showAminosArrays(&$aAminoAcidCodes, &$aAminoAcidCodesLeft, &$aAminoAcidCodesRight)
    {
        $aAminoAcidCodes        = $this->aAminos;
        $aAminoAcidCodesLeft    = array_slice($aAminoAcidCodes, 0, 13);
        $aAminoAcidCodesRight   = array_slice($aAminoAcidCodes, 13);
    }


    /**
     * Treatment when chosen custom
     * Unit Tests OK
     * @param   int         $iFrames
     * @param   string      $sSequence
     * @param   string      $sMycode
     * @return  array
     * @throws \Exception
     */
    public function customTreatment($iFrames, $sSequence, $sMycode)
    {
        try {
            $aFrames = [];
            // Translate in  5-3 direction
            $aFrames[1] = $this->translateDNAToProteinCustomcode(substr($sSequence, 0, floor(strlen($sSequence)/3)*3), $sMycode);

            if ($iFrames > 1) {
                $aFrames[2] = $this->translateDNAToProteinCustomcode(substr($sSequence, 1,floor((strlen($sSequence)-1)/3)*3),$sMycode);
                $aFrames[3] = $this->translateDNAToProteinCustomcode(substr($sSequence, 2,floor((strlen($sSequence)-2)/3)*3),$sMycode);
            }
            // Translate the complementary sequence
            if ($iFrames > 3) {
                // Get complementary
                $this->sRvSequence = $this->compDNA($sSequence);
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
     * Unit Tests OK
     * @param   int             $iFrames
     * @param   string          $sGeneticCode
     * @param   string          $sSequence
     * @return  array
     * @throws  \Exception
     */
    public function definedTreatment($iFrames, $sGeneticCode, $sSequence)
    {
        try {
            // Translate in 5-3 direction
            $aFrames[1] = $this->translateDNAToProtein(substr($sSequence, 0, floor(strlen($sSequence)/3)*3), $sGeneticCode);
            if ($iFrames > 1){
                $aFrames[2] = $this->translateDNAToProtein(substr($sSequence, 1,floor((strlen($sSequence)-1)/3)*3), $sGeneticCode);
                $aFrames[3] = $this->translateDNAToProtein(substr($sSequence, 2,floor((strlen($sSequence)-2)/3)*3), $sGeneticCode);
            }
            // Translate the complementary sequence
            if ($iFrames > 3){
                // Get complementary
                $this->sRvSequence = $this->compDNA($sSequence);
                //calculate frames 4-6
                $aFrames[4] = $this->translateDNAToProtein(substr($this->sRvSequence, 0,floor(strlen($this->sRvSequence)/3)*3), $sGeneticCode);
                $aFrames[5] = $this->translateDNAToProtein(substr($this->sRvSequence, 1,floor((strlen($this->sRvSequence)-1)/3)*3), $sGeneticCode);
                $aFrames[6] = $this->translateDNAToProtein(substr($this->sRvSequence, 2,floor((strlen($this->sRvSequence)-2)/3)*3), $sGeneticCode);
            }
            return $aFrames;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Find ORFs in sequence
     * Unit tests OK
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
     * Translates a DNA Sequence to proteins
     * Unit Tests OK
     * @param   string          $sSequence
     * @param   string          $sGeneticCode
     * @return  string
     * @throws  \Exception
     */
    public function translateDNAToProtein($sSequence, $sGeneticCode)
    {
        try {
            $aAminoAcids = ["F","L","I","M","V","S","P","T","A","Y","*","H","Q","N","K","D","E","C","W","R","G","X"];
            // place a space after each triplete in the sequence
            $temp = chunk_split($sSequence,3,' ');
            // replace triplets by corresponding amnoacid
            $sPeptide = preg_replace($this->aTriplets[$sGeneticCode], $aAminoAcids, $temp);
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
