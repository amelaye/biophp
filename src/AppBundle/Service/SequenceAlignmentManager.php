<?php
/**
 * Sequence Alignment Managing
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 22 september 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Sequence;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

/**
 * Represents the result of an alignment performed by various third-party
 * software such as ClustalW. The alignment is usually found in a file that uses
 * a particular format. Right now, my code supports only FASTA and CLUSTAL formats.
 * Properties and methods allow users to perform post-alignment operations, manipulations, etc.
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @todo : envisager le tableau de sequences comme iterator
 * @todo : length doit représenter la totalité des séquences
 */
class SequenceAlignmentManager
{
    /**
     * Dependency injection for the Sequences Services
     * @var SequenceManager
     */
    private $sequenceManager;

    /**
     * Letters of the alphabet
     * @var array
     */
    private $aAlphabet;

    /**
     * The length of the longest sequence in the alignment set.
     * @var int
     */
    private $iLength;

    /**
     * The number of sequences in the alignment set.
     * @var int
     */
    private $iSeqCount;

    /**
     * The total number of gaps ("-") in all sequences in the alignment set.
     * @var int
     */
    private $iGapCount;

    /**
     * An array containing all the sequences in the alignment set.
     * @var array
     */
    private $aSeqSet;

    /**
     * A boolean or logical value: TRUE if all the sequences in the alignment have the same length, FALSE otherwise.
     * @var bool
     */
    private $bFlush;

    /**
     * Filename of the original parsed file.
     * @var string
     */
    private $sFilename;

    /**
     * Format of the original parsed file.
     * @var string
     */
    private $sFormat;

    /**
     * SequenceAlignmentManager constructor.
     * @param SequenceManager $sequenceManager
     */
    public function __construct(SequenceManager $sequenceManager)
    {
        $this->sequenceManager = $sequenceManager;
        $this->iSeqCount       = 0;
        $this->iLength         = 0;
        $this->iGapCount       = 0;
        $this->bFlush          = true;
        $this->aSeqSet         = array();
        $this->aAlphabet       = range('A','Z');
    }

    /**
     * Sets a specific filename : the file to parse
     * @param   string  $sFilename
     */
    public function setFilename($sFilename)
    {
        $this->sFilename = $sFilename;
    }

    /**
     * Sets a specific format : FASTA or CLUSTAL
     * @param   string  $sFormat
     */
    public function setFormat($sFormat)
    {
        $this->sFormat = $sFormat;
    }

    /**
     * Parses Clustal Files and create Sequence object
     * @throws  \Exception
     */
    public function parseClustal()
    {
        try {
            $fLines        = file($this->sFilename);
            $iLineCount    = $iLastLength = $iLength = $iGapCount = 0;
            $aNameList     = $aSequences = [];
            $aLines        = new \ArrayIterator($fLines);

            foreach($aLines as $sLine) {
                $iLineCount++;
                if ($iLineCount == 1) {
                    continue;
                }
                if (strlen(trim($sLine)) == 0) {
                    continue; // ignore blank lines.
                }

                $aWords = explode(" ", $sLine);
                $aWordLines = [];
                foreach($aWords as $sWord) {
                    if($sWord != "") {
                        $aWordLines[] = str_replace("\n","",$sWord);
                    }
                }
                $sSeqName = $aWordLines[0];
                $sSeqLine = $aWordLines[1];

                if(sizeof($aWordLines) == 2) {
                    if (!in_array($sSeqName, $aNameList)) {
                        $aNameList[] = $sSeqName;
                        $aSequences[$sSeqName] = $sSeqLine;
                    } else {
                        $aSequences[$sSeqName] .= trim($sSeqLine);
                    }
                }
            }

            foreach($aSequences as $sKey => $sSeqData) {
                $oSequence = new Sequence();
                $oSequence->setId($sKey);
                $oSequence->setSeqlength(strlen($sSeqData));
                $oSequence->setSequence($sSeqData);
                $oSequence->setStart(0);
                $oSequence->setEnd(strlen($sSeqData) - 1);

                $iLength += strlen($sSeqData);
                $this->sequenceManager->setSequence($oSequence);
                $iGapCount += $this->sequenceManager->symfreq("-");
                array_push($this->aSeqSet, $oSequence);
            }
            $this->iSeqCount = count($aNameList);
            $this->iLength   = $iLength;
            $this->iGapCount = $iGapCount;
            $this->bFlush    = true;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Parses Fasta Files and create Sequence object
     * @throws  \Exception
     * @Todo : setting start and stop
     */
    public function parseFasta()
    {
        try {
            $fLines      = file($this->sFilename);
            $iSeqCount   = $iMaxLength = $iGapCount = $iPrevId = $iPrevLength = 0;
            $bSameLength = true;
            $sSequence   = "";
            $aLines      = new \ArrayIterator($fLines);

            foreach($aLines as $sLine) {
                if (substr($sLine, 0, 1) == ">") {
                    $iSeqCount++;
                    $iSeqLength = strlen($sSequence);

                    $oSequence = new Sequence();
                    $oSequence->setId($iPrevId);
                    $oSequence->setSeqlength($iSeqLength);
                    $oSequence->setSequence($sSequence);

                    $this->sequenceManager->setSequence($oSequence);
                    $iGapCount += $this->sequenceManager->symfreq("-");

                    if ($iSeqCount > 1) {
                        if ($iSeqLength > $iMaxLength) {
                            $iMaxLength = $iSeqLength;
                        }
                        if (($iSeqCount >= 3) && ($iSeqLength != $iPrevLength)) {
                            $bSameLength = false;
                        }
                        array_push($this->aSeqSet, $oSequence);
                    }

                    $aWords = preg_split("/[\|\/]/", substr($sLine, 1));
                    if(isset($aWords[1])) {
                        $iPrevId = $aWords[1];
                    }

                    $iPrevLength = $iSeqLength;
                    continue;
                } else {
                    $sSequence = $sSequence . trim($sLine);
                }
            }

            $iSeqLength = strlen($sSequence);

            $oSequence = new Sequence();
            $oSequence->setId($iPrevId);
            $oSequence->setSeqlength($iSeqLength);
            $oSequence->setSequence($sSequence);

            $this->sequenceManager->setSequence($oSequence);
            $iGapCount += $this->sequenceManager->symfreq("-");

            if ($iSeqCount >= 1) {
                if ($iSeqLength > $iMaxLength) {
                    $iMaxLength = $iSeqLength;
                }
                if (($iSeqCount >= 3) && ($iSeqLength != $iPrevLength)) {
                    $bSameLength = false;
                }
                array_push($this->aSeqSet, $oSequence);
            }

            $this->iSeqCount = $iSeqCount;
            $this->iLength   = $iMaxLength;
            $this->iGapCount = $iGapCount;
            $this->bFlush    = $bSameLength;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Main method - Parses FASTA or CLUSTAL file
     * This "fetches" all sequences into the $aSeqSet property of the service.
     */
    public function parseFile()
    {
        if ($this->sFormat == "FASTA") {
            $this->parseFasta();
        } elseif ($this->sFormat == "CLUSTAL") {
            $this->parseClustal();
        }
    }

    /**
     * Rearranges the sequences in an alignment set alphabetically by their sequence id.
     * In addition, you can specify if you wish it be in ascending or descending order via $sOption.
     * The sOption accepts either "ASC" or "DESC" in whatever case (uppercase, lowercase,
     * mixed case). This determines the sort order of the alignment set.
     * @param   string          $sOption    ASCendant or DESCendant
     * @throws  \Exception
     */
    public function sortAlpha($sOption = "ASC")
    {
        try {
            $aSequences = [];
            foreach($this->aSeqSet as $oSequence) {
                $key = $oSequence->getId() . str_pad($oSequence->getStart(), 9, "0", STR_PAD_LEFT);
                $aSequences[$key] = $oSequence;
            }

            $sOption = strtoupper($sOption);
            if ($sOption == "ASC") {
                asort($aSequences);
            } elseif ($sOption == "DESC") {
                arsort($aSequences);
            } else {
                throw new \Exception("Invalid argument #1 passed to SORT_ALPHA() method!");
            }

            $aSequences2 = [];
            foreach($aSequences as $sSequence) {
                $aSequences2[] = $sSequence;
            }
            $this->aSeqSet = $aSequences2;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Retrieves a particular sequence (identified by its index number) from an alignment set.
     * @param   string      $sIndex     The index of the array searched
     * @return  Sequence | null
     * @throws  \Exception
     */
    public function fetch($sIndex = "")
    {
        try {
            if (isset($this->aSeqSet[$sIndex])) {
                $oSequence = $this->aSeqSet[$sIndex];
                return $oSequence;
            } else {
                return null;
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Returns the length of the longest sequence in an alignment set.
     * @return  int
     * @throws  \Exception
     */
    public function getMaxiLength()
    {
        try {
            $iMaxLen = 0;
            foreach($this->aSeqSet as $oSequence) {
                if ($oSequence->getSeqlength() > $iMaxLen) {
                    $iMaxLen = $oSequence->getSeqlength();
                }
            }
            return $iMaxLen;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Counts the number of gaps ("-") found in all sequences in an alignment set.
     * @return  int         The number of "gap characters" in the all sequences in the alignment set.
     * @throws  \Exception
     */
    public function getGapCount()
    {
        try {
            $iGapsCount = 0;
            foreach($this->aSeqSet as $oSequence) {
                $this->sequenceManager->setSequence($oSequence);
                $iGapsCount += $this->sequenceManager->symfreq("-");
            }
            return $iGapsCount;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Tests if all the sequences in an alignment set have the same length.
     * @return  boolean
     * @throws  \Exception
     */
    public function getIsFlush()
    {
        try {
            $bSameLength = true;
            $ctr         = 0;
            $iPrevLength = 0;
            foreach($this->aSeqSet as $oSequence) {
                $ctr++;
                $iCurLength = $oSequence->getSeqLength();
                if ($ctr == 1) {
                    $iPrevLength = $iCurLength;
                    continue;
                }
                if ($iCurLength != $iPrevLength) {
                    $bSameLength = false;
                    break;
                }
                $iPrevLength = $iCurLength;
            }
            return $bSameLength;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Returns the character found at a given residue number in a given sequence.
     * @param   int         $iSeqIdx    Index of the sequence in the array
     * @param   int         $iRes       The residue number of the character we wish to get or extract
     * @return  boolean | string        A single character representing an amino acid residue or a "gap".
     * @throws  \Exception
     */
    public function charAtRes($iSeqIdx, $iRes)
    {
        try {
             $iNonGapCount = $iLength = 0;
             return $this->validationRes($iSeqIdx, $iRes, $iNonGapCount, $iLength, "charAtRes");
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Gets the substring between two residues in a sequence that is part of an alignment set.
     * @param   int            $iSeqIdx     Index of the sequence in the array
     * @param   int            $iResStart   Start of the subsequence
     * @param   int            $iResEnd     End of the subsequence
     * @return  string | boolean            A substring within the specified sequence.
     * @throws  \Exception
     */
    public function substrBwRes($iSeqIdx, $iResStart, $iResEnd = 0)
    {
        try {
            $iNonGapCtr   = 0;
            $sSubSequence = "";

            $oSequence = $this->aSeqSet[$iSeqIdx];
            // Later, you can return a code which identifies the type of error.
            if ($iResEnd > $oSequence->getEnd()) {
                return false;
            }
            if ($iResStart < $oSequence->getStart()) {
                return false;
            }
            if ($iResEnd == 0) {
                $iResEnd = $oSequence->getEnd();
            }

            $iResStartCtr = $iResStart - $oSequence->getStart() + 1;
            $iResEndCtr   = $iResEnd - $oSequence->getStart() + 1;
            $iLength      = $oSequence->getSeqLength();

            for($x = 0; $x < $iLength; $x++) {
                $currlet = substr($oSequence->getSequence(), $x, 1);
                if ($currlet != "-") {
                    $iNonGapCtr++;
                }
                if (($iNonGapCtr >= $iResStartCtr) && ($iNonGapCtr <= $iResEndCtr)) {
                    $sSubSequence .= $currlet;
                } elseif ($iNonGapCtr > $iResEndCtr) {
                    break;
                }
            }
            return $sSubSequence;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Converts a column number to a residue number in a sequence that is part of an alignment set.
     * @param   int     $iSeqIdx    Index number of the desired sequence within the alignment set.
     * @param   int     $iCol       The column number which we want to convert to a residue number.
     * @return  boolean|string      An integer representing the residue number corresponding to the given column number.
     * @throws  \Exception
     */
    public function colToRes($iSeqIdx, $iCol)
    {
        try {
            $sCurrLet       = "";
            $iNonGapCount   = 0;

            $oSequence = $this->aSeqSet[$iSeqIdx];
            // Later, you can return a code which identifies the type of error.
            if ($iCol > $oSequence->getSeqLength() - 1) {
                return false;
            }
            if ($iCol < 0) {
                return false;
            }

            for($i = 0; $i <= $iCol; $i++) {
                $sCurrLet = substr($oSequence->getSequence(), $i, 1);
                if ($sCurrLet != "-") {
                    $iNonGapCount++;
                }
            }
            if ($sCurrLet == "-") {
                return "-";
            } else {
                return ($oSequence->getStart() + $iNonGapCount - 1);
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Converts a residue number to a column number in a sequence in an alignment set.
     * @param   int     $iSeqIdx    The index number of the desired sequence in the alignment set.
     * @param   int     $iRes       The residue number we wish to convert into a column number.
     * @return  boolean|int         An integer representing the column number corresponding to the given residue number.
     * @throws  \Exception
     */
    public function resToCol($iSeqIdx, $iRes)
    {
        try {
            $iNonGapCount = $iLength = 0;
            return $this->validationRes($iSeqIdx, $iRes, $iNonGapCount, $iLength, "resToCol");
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Creates a new alignment set from a series of contiguous/consecutive sequences.
     * @param   int     $iStart     The index number of the first sequence to include in the new SeqAlign object.
     * @param   int     $iEnd       The index number of the last sequence to include in the new SeqAlign object.
     * @throws  \Exception
     */
    public function subalign($iStart, $iEnd)
    {
        try {
            if (($iStart < 0) or ($iEnd < 0)) {
                throw new \Exception("Invalid argument passed to SUBALIGN() method!");
            }
            if (($iStart > $this->iSeqCount - 1) or ($iEnd > $this->iSeqCount - 1)) {
                throw new \Exception("Invalid argument passed to SUBALIGN() method!");
            }

            $this->aSeqSet      = array_slice($this->aSeqSet, $iStart, $iEnd - $iStart + 1);
            $this->iLength      = $this->getMaxiLength();
            $this->iSeqCount    = $iEnd - $iStart + 1;
            $this->iGapCount    = $this->getGapCount();
            $this->bFlush       = $this->getIsFlush();
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Creates a new alignment set from non-consecutive sequences found in another existing alignment set.
     * @throws \Exception
     * @Todo : length is wrong
     */
    public function select()
    {
        try {
            $iCtr       = 0;
            $aNewSeqSet = array();
            $aArgs      = func_get_args();

            if (count($aArgs) == 0) {
                throw new \Exception("Must pass at least one argument to SELECT() method!");
            }
            foreach($aArgs as $iSeqIdx) {
                $oSequence = $this->aSeqSet;
                $aNewSeqSet[] = $oSequence[$iSeqIdx];
                $iCtr++;
            }

            $this->aSeqSet      = $aNewSeqSet;
            $this->iLength      = $this->getMaxiLength();
            $this->iSeqCount    = count($aArgs);
            $this->iGapCount    = $this->getGapCount();
            $this->bFlush       = $this->getIsFlush();
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Determines the index position of both variant and invariant residues according
     * to a given "percentage threshold" similar to that in the consensus() method.
     * @param   int         $iThreshold    a number between 0 to 100, indicating the percentage threshold below
     * which the current index position is considered variant, and on or above which the current
     * index position is considered invariant. If omitted, this is set to 100 by default.
     * @return  array
     * @throws  \Exception
     */
    public function resVar($iThreshold = 100)
    {
        try {
            $aAllPos     = $aInvarPos = $aVarPos = [];
            $aGlobFreq   = array();
            $aSeqSet     = $this->aSeqSet;
            $oFirstSeq   = $aSeqSet[0];
            $iSeqLength  = strlen($oFirstSeq->getSequence());

            for($i = 0; $i < count($this->aAlphabet); $i++) {
                $sCurrLet = $this->aAlphabet[$i];
                $aGlobFreq[$sCurrLet] = 0;
            }

            for($i = 0; $i < $iSeqLength; $i++) {
                $aKeys = [];
                $iMaxPercent = $this->calcMaxPercent($aGlobFreq, $aSeqSet, $i, $aKeys);
                if ($iMaxPercent >= $iThreshold) {
                    array_push($aInvarPos, $i);
                } else {
                    array_push($aVarPos, $i);
                }
            }
            $aAllPos["INVARIANT"] = $aInvarPos;
            $aAllPos["VARIANT"]   = $aVarPos;
            return $aAllPos;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Returns the consensus string for an alignment set.  See technical reference for details.
     * @param   int         $iThreshold     A number between 0 to 100, indicating the percentage threshold before
     * (or below which) the unknown character "?" is used in a particular position or column in the
     * consensus string. If omitted, this is set to 100 by default.
     * @return  string                      The consensus string formed according to the given threshold.
     * @throws  \Exception
     */
    public function consensus($iThreshold = 100)
    {
        try {
            $aSeqSet     = $this->aSeqSet;
            $sResult     = "";
            $oFirstSeq   = $aSeqSet[0];
            $iSeqLength  = strlen($oFirstSeq->getSequence());
            $aGlobFreq   = [];

            for($i = 0; $i < count($this->aAlphabet); $i++) {
                $sCurrLet = $this->aAlphabet[$i];
                $aGlobFreq[$sCurrLet] = 0;
            }

            for($i = 0; $i < $iSeqLength; $i++) {
                $aKeys = [];
                $iMaxPercent = $this->calcMaxPercent($aGlobFreq, $aSeqSet, $i, $aKeys);
                if ($iMaxPercent >= $iThreshold) {
                    $sResult = $sResult . $aKeys[0];
                } else {
                    $sResult = $sResult . "?";
                }
            }
            return $sResult;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Adds a sequence to an alignment set. It does not perform any sequence alignment.
     * @param   Sequence     $oSequence     The object to be added to the alignment set.
     * @return  int                         The number of sequences in the alignment set after the call.
     * @throws  \Exception
     */
    public function addSequence($oSequence)
    {
        try {
            if (is_object($oSequence)) {
                array_push($this->aSeqSet, $oSequence);
                if ($oSequence->getSeqLength() > $this->iLength) {
                    $this->iLength = $oSequence->getSeqLength();
                }

                $this->sequenceManager->setSequence($oSequence);
                $this->iGapCount = $this->iGapCount + $this->sequenceManager->symfreq("-");
                if ($oSequence->getSeqLength() > $this->iLength) {
                    $this->iLength = $oSequence->getSeqLength();
                }

                if ($this->bFlush) {
                    if ($this->iSeqCount >= 1) {
                        $oFirstSeq = $this->aSeqSet[0];
                        if ($oSequence->getSeqLength() != $oFirstSeq->getSeqLength()) {
                            $this->bFlush = false;
                        }
                    }
                }

                $this->iSeqCount++;
                return count($this->aSeqSet);
            } else  {
                throw new InvalidTypeException("Please give a Sequence object !");
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Deletes or removes a sequence from an alignment set.
     * @param   string      $iSequenceId    The id of the sequence to be deleted from the alignment set.
     * @return  int                         The number of sequences in the alignment set after the call.
     * @throws  \Exception
     */
    public function deleteSequence($iSequenceId)
    {
        try {
            $aTempSet = array();
            $oRemovedSeq = new Sequence();
            $iPrevLength = 0;

            foreach($this->aSeqSet as $oElement) {
                if ($oElement->getId() != $iSequenceId) {
                    array_push($aTempSet, $oElement);
                } else {
                    $oRemovedSeq = $oElement;
                }
            }
            // Updates the value of the SEQSET property of the SEQALIGN object.
            $this->aSeqSet = $aTempSet;
            // Updates the value of the SEQ_COUNT property of the SEQALIGN object.
            $this->iSeqCount = $this->iSeqCount - 1;

            // Updates the value of the LENGTH property of the SEQALIGN object.
            if ($oRemovedSeq->getSeqLength() == $this->iLength) {
                $iMaxLength = 0;
                foreach($this->aSeqSet as $oElement) {
                    if ($oElement->getSeqLength() > $iMaxLength) {
                        $iMaxLength = $oElement->getSeqLength();
                    }
                }
                $this->iLength = $iMaxLength;
            }
            // Updates the value of the GAP_COUNT property of the SEQALIGN object.
            $this->sequenceManager->setSequence($oRemovedSeq);
            $this->iGapCount = $this->iGapCount - $this->sequenceManager->symfreq("-");
            // Updates the value of the IS_FLUSH property of the SEQALIGN object.
            if (!$this->bFlush) {
                // Take note that seq_count has already been decremented in the code above.
                if ($this->iSeqCount <= 1) {
                    $this->bFlush = true;
                } else {
                    $bSameLength = true;
                    $iCtr = 0;
                    foreach($this->aSeqSet as $oElement) {
                        $iCtr++;
                        $iCurrLength = $oElement->getSeqLength();
                        if ($iCtr == 1) {
                            $iPrevLength = $iCurrLength;
                            continue;
                        }
                        if ($iCurrLength != $iPrevLength) {
                            $bSameLength = false;
                            break;
                        }
                        $iPrevLength = $iCurrLength;
                    }
                    if ($bSameLength) {
                        $this->bFlush = true;
                    }
                }
            }
            // Return the new number of sequences in the alignment set AFTER delete operation.
            return count($this->aSeqSet);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Fetches something found at a given residue number in a given sequence
     * @param   int         $iSeqIdx        The index number of the desired sequence in the alignment set.
     * @param   int         $iRes           The residue number we wish to convert.
     * @param   int         $iNonGapCount   Number of non-gap characters
     * @param   int         $iLength        Length of a sequence
     * @param   string      $sContext       The original function calling
     * @return  bool|int|string
     */
    private function validationRes($iSeqIdx, $iRes, &$iNonGapCount, &$iLength, $sContext)
    {
        $iNonGapCtr = 0;
        $oSequence   = $this->aSeqSet[$iSeqIdx];

        if ($iRes > $oSequence->getEnd()) {
            return false;
        }
        if ($iRes < $oSequence->getStart()) {
            return false;
        }
        $iLength      = $oSequence->getSeqLength();
        $iNonGapCount = $iRes - $oSequence->getStart() + 1;

        for($x = 0; $x < $iLength; $x++) {
            $sCurrLet = substr($oSequence->getSequence(), $x, 1);
            if ($sCurrLet == "-") {
                continue;
            } else {
                $iNonGapCtr++;
                if ($iNonGapCtr == $iNonGapCount) {
                    if ($sContext == "resToCol") {
                        return $x;
                    }
                    else if ($sContext == "charAtRes") {
                        return $sCurrLet;
                    }
                }
            }
        }
    }

    /**
     * Calculates the max percentage of frequencies
     * @param   array       $aGlobFreq      Array of frequencies of the letters
     * @param   array       $aSeqSet        Array of sequencies
     * @param   int         $i              Current iteration
     * @param   array       $aKeys          Keys of the array of frequencies
     * @return  float|int
     */
    private function calcMaxPercent($aGlobFreq, $aSeqSet, $i, &$aKeys)
    {
        $aFrequences = $aGlobFreq;
        for($j = 0; $j < $this->iSeqCount; $j++) {
            $oCurrSeq = $aSeqSet[$j];
            $sCurrLet = substr($oCurrSeq->getSequence(), $i, 1);
            if(isset($aFrequences[$sCurrLet])) {
                $aFrequences[$sCurrLet]++;
            } else {
                $aFrequences[$sCurrLet] = 1;
            }
        }
        arsort($aFrequences);
        $aKeys = array_keys($aFrequences);
        $iMaxPercent = ($aFrequences[$aKeys[0]]/$this->iSeqCount) * 100;

        return $iMaxPercent;
    }
}
