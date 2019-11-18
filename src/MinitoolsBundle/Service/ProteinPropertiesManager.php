<?php
/**
 * Proteins properties Functions
 * Inspired by BioPHP's project biophp.org
 * Created 24 february 2019
 * Last modified 15 august 2019
 */
namespace MinitoolsBundle\Service;

use AppBundle\Api\Bioapi;

/**
 * Class ProteinPropertiesManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ProteinPropertiesManager
{
    /**
     * @var Bioapi
     */
    private $bioapi;

    /**
     * @var array
     */
    private $aminos;

    /**
     * @var array
     */
    private $pk;

    /**
     * ProteinPropertiesManager constructor.
     * @param   Bioapi  $bioapi
     */
    public function __construct(
        Bioapi $bioapi
    ){
        $this->bioapi                   = $bioapi;
        $this->aminos                   = $bioapi->getAminos();
    }

    /**
     * Get pk values for charged aminoacids
     * @param string $dataSource
     */
    public function setPk($dataSource)
    {
        $this->pk = $this->bioapi->getPkValueById($dataSource);
    }

    /**
     * Converts a subsequence into 3 letters code
     * @param   string      $sSubsequence
     * @return  string
     * @throws  \Exception
     */
    public function convertInto3lettersCode($sSubsequence)
    {
        try {
            $s3LetterCode = "";
            foreach(str_split($sSubsequence) as $sLetter) {
                $s3LetterCode .= $this->aminos[$sLetter]["name3Letters"];
            }
            return $s3LetterCode;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Generates subsequence string
     * @param   int     $iStart
     * @param   int     $iEnd
     * @param   string  $sSequence
     * @return  string
     * @throws  \Exception
     */
    public function writeSubsequence($iStart, $iEnd, $sSequence)
    {
        try {
            $sSubsequence = "";
            // if subsequence is requested
            if ($iStart != "" || $iEnd != "") {
                $start = ($iStart != "") ? $iStart - 1 : 0;
                $end  = ($iEnd != "") ? $iEnd : strlen($sSequence);
                $sSubsequence = substr($sSequence, $start,$end - $start);
            }
            return $sSubsequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * At isoelectric point, charge of protein will be
     * To calculate pH where charge is 0 a loop is required
     * The loop will start computing charge of protein at pH=7, and if charge is not 0, new charge value will be computed
     * by using a different pH. Procedure will be repeated until charge is 0 (at isoelectric point)
     * @param       array       $aAminoacidContent
     * @return      float
     * @throws      \Exception
     */
    public function proteinIsoelectricPoint($aAminoacidContent)
    {
        try {
            $iPH = 7;          // pH value at start
            $iDelta = 4;       // this parameter will be used to modify pH when charge!=0. The value of $delta will change during the loop
            while(1) {
                // compute charge of protein at corresponding pH (uses a function)
                $iCharge = $this->proteinCharge($aAminoacidContent, $iPH);
                // check whether $charge is 0 (consecuentely, pH will be the isoelectric point
                if (round($iCharge,4) == 0) {
                    break;
                }
                // next line to check how computation is perform
                // modify pH for next round
                if ($iCharge > 0) {
                    $iPH = $iPH + $iDelta;
                } else {
                    $iPH = $iPH - $iDelta;
                }
                // reduce value for $iDelta
                $iDelta = $iDelta/2;
            }
            // return pH at which charge=0 (the isoelectric point) with two decimals
            $fRoundPH = round($iPH,2);
            return $fRoundPH;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Computes partial charge
     * @param       float     $fVal1
     * @param       int       $iVal2
     * @return      float
     * @throws      \Exception
     */
    public function partialCharge($fVal1, $iVal2)
    {
        try {
            $iCr = pow(10,$fVal1 - $iVal2); // compute concentration ratio
            $fPc = $iCr / ($iCr + 1); // compute partial charge
            return $fPc;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Computes protein charge at corresponding pH
     * @param       array       $aAminoacidContent
     * @param       int         $iPH
     * @return      float
     * @throws      \Exception
     */
    function proteinCharge($aAminoacidContent, $iPH)
    {
        try {
            $iCharge = $this->partialCharge($this->pk["NTERMINUS"], $iPH);
            $iCharge+= $this->partialCharge($this->pk["K"], $iPH) * $aAminoacidContent["K"];
            $iCharge+= $this->partialCharge($this->pk["R"], $iPH) * $aAminoacidContent["R"];
            $iCharge+= $this->partialCharge($this->pk["H"], $iPH) * $aAminoacidContent["H"];
            $iCharge-= $this->partialCharge($iPH, $this->pk["D"]) * $aAminoacidContent["D"];
            $iCharge-= $this->partialCharge($iPH, $this->pk["E"]) * $aAminoacidContent["E"];
            $iCharge-= $this->partialCharge($iPH, $this->pk["C"]) * $aAminoacidContent["C"];
            $iCharge-= $this->partialCharge($iPH, $this->pk["Y"]) * $aAminoacidContent["Y"];
            $iCharge-= $this->partialCharge($iPH, $this->pk["CTERMINUS"]);
            return $iCharge;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param   array $aAminoacidContent
     * @return  array
     * @throws  \Exception
     */
    public function formatAminoacidContent($aAminoacidContent)
    {
        try {
            $results = [];
            foreach($aAminoacidContent as $aa => $count) {
                $results[] = ["one_letter" => $aa, "three_letters" => $this->aminos[$aa]["name3Letters"], "count" => $count];
            }
            return $results;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Counts number of amino acids in a sequence
     * @param       string      $sSequence
     * @return      array
     * @throws      \Exception
     */
    public function aminoacidContent($sSequence)
    {
        try {
            $aNbAminos = [];
            foreach($this->aminos as $aAminosData) {
                if(isset($aAminosData["name3Letters"]) && $aAminosData["name3Letters"] != "N/A") {
                    $aNbAminos[$aAminosData["name1Letter"]] = 0;
                }
            }

            for($i = 0; $i < strlen($sSequence); $i++){
                $sSequence2 = substr($sSequence, $i,1);
                $aNbAminos[$sSequence2]++;
            }
            return $aNbAminos;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Prediction of the molar absorption coefficient of a protein
     * Pace et al. . Protein Sci. 1995;4:2411-23.
     * @param   array       $aAminoacidContent
     * @param   float       $fMolWeight
     * @return  float
     * @throws  \Exception
     */
    public function molarAbsorptionCoefficientOfProt($aAminoacidContent, $fMolWeight)
    {
        try {
            $abscoef = (
                $aAminoacidContent["A"] * 5500
                + $aAminoacidContent["Y"] * 1490
                + $aAminoacidContent["C"] * 125
                ) / $fMolWeight;
            return $abscoef;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Molecular weight calculation
     * @param   array   $aAminoacidContent
     * @return  float
     * @throws  \Exception
     */
    public function proteinMolecularWeight($aAminoacidContent)
    {
        try {
            $fMolWeight = 18.02;  // water
            foreach($aAminoacidContent as $key => $sAmino) {
                $fMolWeight += $sAmino * $this->aminos[$key]["residueMolWeight"];
            }
            return $fMolWeight;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Displays the colored aminoacids
     * @param       string      $sSequence
     * @param       array       $aColors
     * @return      array
     * @throws      \Exception
     */
    public function proteinAminoacidNature1($sSequence, $aColors)
    {
        try {
            $aResult = [];
            for($i = 0; $i < strlen($sSequence); $i++) {
                // non-polar aminoacids, yellow
                if (strpos(" GAPVILFM", substr($sSequence,$i,1)) > 0) {
                    $aResult[] = [substr($sSequence,$i,1), $aColors["nonpolar"]];
                    continue;
                }
                // polar aminoacids, magenta
                if (strpos(" SCTNQHYW", substr($sSequence,$i,1)) > 0) {
                    $aResult[] = [substr($sSequence,$i,1), $aColors["polar"]];
                    continue;
                }
                // charged aminoacids, red
                if (strpos(" DEKR", substr($sSequence,$i,1)) > 0) {
                    $aResult[] = [substr($sSequence,$i,1), $aColors["charged"]];
                    continue;
                }
            }
            return $aResult;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Displays the colored aminoacids (tyoe 2)
     * @param   string      $sSequence
     * @param   array       $aColors
     * @return  array
     * @throws  \Exception
     */
    public function proteinAminoacidNature2($sSequence, $aColors)
    {
        try {
            $aResult = [];
            for ($i = 0; $i < strlen($sSequence); $i++) {
                // Small nonpolar (yellow)
                if (strpos(" GAST",substr($sSequence,$i,1)) > 0) {
                    $aResult[] = [substr($sSequence,$i,1), $aColors["nonpolar"]];
                    continue;
                }
                // Small hydrophobic (green)
                if (strpos(" CVILPFYMW",substr($sSequence,$i,1)) > 0) {
                    $aResult[] = [substr($sSequence,$i,1), $aColors["hydrophobic"]];
                    continue;
                }
                // Polar
                if (strpos(" DQH",substr($sSequence,$i,1)) > 0) {
                    $aResult[] = [substr($sSequence,$i,1), $aColors["polar"]];
                    continue;
                }
                // Negatively charged
                if (strpos(" NE",substr($sSequence,$i,1)) > 0) {
                    $aResult[] = [substr($sSequence,$i,1), $aColors["negatively_charged"]];
                    continue;
                }
                // Positively charged
                if (strpos(" KR",substr($sSequence,$i,1)) > 0) {
                    $aResult[] = [substr($sSequence,$i,1), $aColors["positively_charged"]];
                    continue;
                }
            }
            return $aResult;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Chemical group/aminoacids:
     * L/GAVLI       Amino Acids with Aliphatic R-Groups
     * H/ST          Non-Aromatic Amino Acids with Hydroxyl R-Groups
     * M/NQ          Acidic Amino Acids
     * R/FYW         Amino Acids with Aromatic Rings
     * S/CM          Amino Acids with Sulfur-Containing R-Groups
     * I/P           Imino Acids
     * A/DE          Acidic Amino Acids
     * C/KRH         Basic Amino Acids
     * $ X/X
     * @param       string   $sAminoSeq
     * @return      string
     * @throws      \Exception
     */
    public function proteinAminoacidsChemicalGroup($sAminoSeq)
    {
        try {
            $sChemgrpSeq = "";
            $iCtr = 0;
            while(1) {
                $sAminoLetter = substr($sAminoSeq, $iCtr, 1);
                if ($sAminoLetter == "") {
                    break;
                }
                if (strpos(" GAVLI", $sAminoLetter) > 0) {
                    $sChemgrpSeq .= "L";
                }
                elseif (($sAminoLetter == "S") || ($sAminoLetter == "T")) {
                    $sChemgrpSeq .= "H";
                }
                elseif (($sAminoLetter == "N") || ($sAminoLetter == "Q")) {
                    $sChemgrpSeq .= "M";
                }
                elseif (strpos(" FYW", $sAminoLetter)>0) {
                    $sChemgrpSeq .= "R";
                }
                elseif (($sAminoLetter == "C") || ($sAminoLetter == "M")) {
                    $sChemgrpSeq .= "S";
                }
                elseif ($sAminoLetter == "P") {
                    $sChemgrpSeq .= "I";
                }
                elseif (($sAminoLetter == "D") || ($sAminoLetter == "E")) {
                    $sChemgrpSeq .= "A";
                }
                elseif (($sAminoLetter == "K") || ($sAminoLetter == "R") || ($sAminoLetter == "H")) {
                    $sChemgrpSeq .= "C";
                }
                elseif ($sAminoLetter == "*") {
                    $sChemgrpSeq .= "*";
                }
                elseif ($sAminoLetter == "X" or $sAminoLetter == "N") {
                    $sChemgrpSeq .= "X";
                }
                else {
                    throw new \Exception("Invalid amino acid symbol in input sequence.");
                }
                $iCtr++;
            }
            return $sChemgrpSeq;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}