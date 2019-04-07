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
}