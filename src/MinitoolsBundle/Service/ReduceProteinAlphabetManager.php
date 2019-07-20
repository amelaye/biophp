<?php
/**
 * Class ReduceProteinAlphabetManager
 * Inspired by BioPHP's project biophp.org
 * Created 27 february 2019 - RIP Pasha =^._.^= ∫
 * Last modified 7 april 2019
 */
namespace MinitoolsBundle\Service;

use AppBundle\Bioapi\Bioapi;

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
     * @param       bioapi  $bioapi
     */
    public function __construct($protein_colors, Bioapi $bioapi)
    {
        $this->protein_colors   = $protein_colors;
        $this->aReductions      = $bioapi->getReductions();
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
            $aPattern       =  $this->aReductions[$sType]["pattern"];
            $aReplacement   =  $this->aReductions[$sType]["reduction"];
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