<?php
/**
 * Protein To DNA Functions
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 6 april 2019
 */
namespace MinitoolsBundle\Service;

/**
 * Class ProteinToDnaManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class ProteinToDnaManager
{
    /**
     * @var array
     */
    private $aTripletsList;

    /**
     * ProteinToDnaManager constructor.
     * @param   array   $aTripletsList
     */
    public function __construct($aTripletsList)
    {
        $this->aTripletsList = $aTripletsList;
    }

    /**
     * @param   string $sSequence
     * @param   string $sGeneticCode
     * @return  string
     * @throws  \Exception
     */
    public function translateProteinToDNA($sSequence, $sGeneticCode)
    {
        try {
            // $aAminoacids is the array of aminoacids
            $aAminoacids = array(
                "(F )","(L )","(I )","(M )",
                "(V )","(S )","(P )","(T )",
                "(A )","(Y )", "(\* )","(H )",
                "(Q )","(N )","(K )","(D )",
                "(E )","(C )","(W )","(R )",
                "(G )","(X )"
            );

            // place a space after each aminoacid in the sequence
            $sTemp = chunk_split($sSequence,1,' ');

            // replace aminoacid by corresponding amnoacid
            $sPeptide = preg_replace($aAminoacids, $this->aTripletsList[$sGeneticCode], $sTemp);

            // return peptide sequence
            return $sPeptide;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}