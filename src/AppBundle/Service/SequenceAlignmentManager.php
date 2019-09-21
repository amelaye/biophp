<?php
/**
 * Sequence Alignment Managing
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 21 september 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Sequence;

/**
 * SeqAlign - represents the result of an alignment performed by various third-party
 * software such as ClustalW.  The alignment is usually found in a file that uses
 * a particular format. Right now, my code supports only FASTA and CLUSTAL formats.
 *
 * SeqAlign properties and methods allow users to perform post-alignment operations,
 * manipulations, etc.
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SequenceAlignmentManager
{
    /**
     * @var SequenceManager
     */
    private $sequenceManager;

    private $aAlphabet;

    /**
     * @var int
     */
    private $iLength;

    /**
     * @var int
     */
    private $iSeqCount;

    /**
     * @var int
     */
    private $iGapCount;

    /**
     * @var array
     */
    private $aSeqSet;

    /**
     * @var bool
     */
    private $bFlush;

    /**
     * @var string
     */
    private $sFilename;

    /**
     * @var string
     */
    private $sFormat;

    public function __construct(SequenceManager $sequenceManager)
    {
        $this->sequenceManager = $sequenceManager;

        $this->iSeqCount = 0;
        $this->iLength = 0;
        $this->iGapCount = 0;
        $this->bFlush = true;
        $this->aSeqSet = array();
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
     */
    public function parseClustal()
    {
        $fLines        = file($this->sFilename);
        $sConserveLine = "";
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

            if (sizeof($aWordLines) == 1) {
                $sConserveLine .= substr($sSeqLine, 0, $iLastLength);
                continue;
            }
            if (!in_array($sSeqName, $aNameList)) {
                $aNameList[] = $sSeqName;
                $aSequences[$sSeqName] = $sSeqLine;
                $iLastLength = strlen(trim($sSeqLine));
            } else {
                $aSequences[$sSeqName] .= trim($sSeqLine);
                $iLastLength = strlen(trim($sSeqLine));
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
    }

    /**
     * Parses Fasta Files and create Sequence object
     */
    public function parseFasta()
    {
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
    }

    /**
     * Main method - Parses FASTA or CLUSTAL file
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
     * In addition, you can specify if you wish it be in ascending or descending order via $option.
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
     * @return  int
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
            $ctr = 0;
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
     * @param   int         $iPos       Position of the element
     * @return  boolean
     * @throws  \Exception
     */
    public function charAtRes($iSeqIdx, $iPos)
    {
        try {
            $iNonGapCtr   = 0;

            $oSequence = $this->aSeqSet[$iSeqIdx];
            if ($iPos > $oSequence->getEnd()) {
                return false;
            }
            if ($iPos < $oSequence->getStart()) {
                return false;
            }

            $iLength      = $oSequence->getSeqLength();
            $iNonGapCount = $iPos - $oSequence->getStart() + 1;

            for($x = 0; $x < $iLength; $x++) {
                $sCurrLet = substr($oSequence->getSequence(), $x, 1);
                if ($sCurrLet == "-") {
                    continue;
                } else {
                    $iNonGapCtr++;
                    if ($iNonGapCtr == $iNonGapCount) {
                        return $sCurrLet;
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Gets the substring between two residues in a sequence that is part of an alignment set.
     * @param   int            $iSeqIdx     Index of the sequence in the array
     * @param   int            $iResStart   Start of the subsequence
     * @param   int            $iResEnd     End of the subsequence
     * @return  string | boolean
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
     * @param type $seqidx
     * @param type $col
     * @return boolean|string
     * @throws \Exception
     * @group Legacy
     */
    public function col2res($seqidx, $col)
    {
        try {
            $oSeqset = $this->seqAlign->getSeqset();
            $seqobj = $oSeqset[$seqidx];
            // Later, you can return a code which identifies the type of error.
            if ($col > $seqobj->seqlen() - 1) {
                return FALSE;
            }
            if ($col < 0) {
                return FALSE;
            }

            $nongap_ctr = 0;
            for($i = 0; $i <= $col; $i++) {
                $currlet = substr($seqobj->sequence, $i, 1);
                if ($currlet != "-") {
                    $nongap_ctr++;
                }
            }
            if ($currlet == "-") {
                return "-";
            } else {
                return ($seqobj->start + $nongap_ctr - 1);
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Converts a residue number to a column number in a sequence in an alignment set.
     * @param type $seqidx
     * @param type $res
     * @return boolean|int
     * @throws \Exception
     * @group Legacy
     */
    public function res2col($seqidx, $res)
    {
        try {
            $oSeqset = $this->seqAlign->getSeqset();
            $seqobj = $oSeqset[$seqidx];
            // Later, you can return a code which identifies the type of error.
            if ($res > $seqobj->end) {
                return FALSE;
            }
            if ($res < $seqobj->start) {
                return FALSE;
            }

            $len = $seqobj->seqlen();
            $nongap_count = $res - $seqobj->start + 1;
            $nongap_ctr = 0;
            for($col = 0; $col < $len; $col++) {
                $currlet = substr($seqobj->sequence, $col, 1);
                if ($currlet == "-") {
                    continue;
                } else {
                    $nongap_ctr++;
                    if ($nongap_ctr == $nongap_count) {
                        return $col;
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Returns a subset of consecutive sequences in an alignment set.
     * @param type $beg
     * @param type $end
     * @return \AppBundle\Services\SeqAlign
     * @throws \Exception
     */
    public function subalign($beg, $end)
    {
        try {
            if (($beg < 0) or ($end < 0)) {
                throw new \Exception("Invalid argument passed to SUBALIGN() method!");
            }
            if (($beg > $this->seqAlign->getSeqCount() - 1) or ($end > $this->seqAlign->getSeqCount() - 1)) {
                throw new \Exception("Invalid argument passed to SUBALIGN() method!");
            }

            $new_align = new SequenceAlignment();
            $new_align->setSeqset(array_slice($this->seqAlign->getSeqset(), $beg, $end-$beg+1));
            $new_align->setLength($new_align->get_length());
            $new_align->setSeqCount($end - $beg + 1);
            $new_align->setGapCount($new_align->get_gap_count());
            $new_align->setSeqptr(0);
            $new_align->setIsFlush($new_align->get_is_flush());

            return $new_align;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Returns a set of (possibly non-consecutive) sequences in an alignment set.
     * @return SequenceAlignment
     * @throws \Exception
     */
    public function select()
    {
        try {
            $arglist = func_get_args();
            if (count($arglist) == 0) {
                throw new \Exception("Must pass at least one argument to SELECT() method!");
            }

            $new_seqset = array();
            $new_align = new SequenceAlignment();
            $ctr = 0;
            foreach($arglist as $seqindex) {
                $oSeqset = $this->seqAlign->getSeqset();
                $new_seqset[] = $oSeqset[$seqindex];
                $ctr++;
            }

            $new_align->setSeqset($new_seqset);
            $new_align->setLength($new_align->get_length());
            $new_align->setSeqCount(count($arglist));
            $new_align->setGapCount($new_align->get_gap_count());
            $new_align->setIsFlush($new_align->get_is_flush());
            $new_align->setSeqptr(0);
            return $new_align;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Identifies the positions of variant and invariant (conserved) residues in an alignment set.
     * @param type $threshold
     * @return array
     * @throws \Exception
     */
    public function res_var($threshold = 100)
    {
        try {
            $all_pos    = [];
            $invar_pos  = [];
            $var_pos    = [];
            $oSeqset    = $this->seqAlign->getSeqset();
            $firstseq   = $oSeqset[0];
            $seqlength  = strlen($firstseq->sequence);

            $globfreq = array();
            for($i = 0; $i < count($this->aAlphabet); $i++) {
                $currlet = $this->aAlphabet[$i];
                $globfreq[$currlet] = 0;
            }

            for($i = 0; $i < $seqlength; $i++) {
                $freq = $globfreq;
                for($j = 0; $j < $this->seqAlign->getSeqCount(); $j++) {
                    $currseq = $oSeqset[$j];
                    $currlet = substr($currseq->sequence, $i, 1);
                    $freq[$currlet]++;
                }
                arsort($freq);
                list($key, $value) = each($freq);
                $maxpercent = ($value/$this->seqAlign->getSeqCount()) * 100;
                if ($maxpercent >= $threshold) {
                    array_push($invar_pos, $i);
                } else {
                    array_push($var_pos, $i);
                }
            }
            $all_pos["INVARIANT"] = $invar_pos;
            $all_pos["VARIANT"] = $var_pos;
            return $all_pos;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Returns the consensus string for an alignment set.  See technical reference for details.
     * @param type $threshold
     * @return string
     * @throws \Exception
     */
    public function consensus($threshold = 100)
    {
        try {
            $oSeqset    = $this->seqAlign->getSeqset();
            $resultstr  = "";
            $firstseq   = $oSeqset[0];
            $seqlength  = strlen($firstseq->sequence);

            $globfreq = [];
            for($i = 0; $i < count($this->aAlphabet); $i++) {
                $currlet = $this->aAlphabet[$i];
                $globfreq[$currlet] = 0;
            }

            for($i = 0; $i < $seqlength; $i++) {
                $freq = $globfreq;
                for($j = 0; $j < $this->seqAlign->getSeqCount(); $j++) {
                    $currseq = $oSeqset[$j];
                    $currlet = substr($currseq->sequence, $i, 1);
                    $freq[$currlet]++;
                }
                arsort($freq);
                list($key, $value) = each($freq);
                $maxpercent = ($value/$this->seqAlign->getSeqCount()) * 100;
                if ($maxpercent >= $threshold) {
                    $resultstr = $resultstr . $key;
                } else {
                    $resultstr = $resultstr . "?";
                }
            }
            return $resultstr;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Adds a sequence to an alignment set.
     * @param type $seqobj
     * @return type
     * @throws \Exception
     */
    public function add_seq($seqobj)
    {
        try {
            if (gettype($seqobj) == "object") {
                array_push($this->seqAlign->getSeqset(), $seqobj);
                if ($seqobj->seqlen() > $this->seqAlign->getLength()) {
                    $this->seqAlign->setLength($seqobj->seqlen());
                }

                $this->seqAlign->setGapCount($this->seqAlign->getGapCount() + $seqobj->symfreq("-"));
                if ($seqobj->seqlen() > $this->seqAlign->getLength()) {
                    $this->seqAlign->setLength($seqobj->seqlen());
                }

                if ($this->seqAlign->getIsFlush()) {
                    if ($this->seqAlign->getSeqCount() >= 1) {
                        $firstseq = $this->seqAlign->getSeqset()[0];
                        if ($seqobj->seqlen() != $firstseq->seqlen()) {
                            $this->seqAlign->setIsFlush(false);
                        }
                    }
                }

                $this->seqAlign->setSeqCount($this->seqAlign->getSeqCount() + 1);
                return count($this->seqAlign->getSeqset());
            } elseif (gettype($seqobj) == "string") {
                print "NOT YET OPERATIONAL";
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Deletes or removes a sequence from an alignment set.
     * @param type $seqobj
     * @return type
     * @throws \Exception
     */
    public function del_seq($seqobj)
    {
        try {
            $seqid = $seqobj;
            $tempset = array();
            foreach($this->seqAlign->getSeqset() as $element) {
                if ($element->id != $seqid) {
                    array_push($tempset, $element);
                } else {
                    $removed_seq = $element;
                }
            }
            $this->seqAlign->setSeqset($tempset); // Updates the value of the SEQSET property of the SEQALIGN object.
            $this->seqAlign->setSeqCount($this->seqAlign->getSeqCount() - 1); // Updates the value of the SEQ_COUNT property of the SEQALIGN object.

            if ($removed_seq->seqlen() == $this->length) { // Updates the value of the LENGTH property of the SEQALIGN object.
                $maxlen = 0;
                foreach($this->seqAlign->getSeqset() as $element) {
                    if ($element->seqlen() > $maxlen) {
                        $maxlen = $element->seqlen();
                    }
                }
                $this->seqAlign->setLength($maxlen);
            }
            // Updates the value of the GAP_COUNT property of the SEQALIGN object.
            $this->seqAlign->setGapCount($this->seqAlign->setGapCount() - $removed_seq->symfreq("-"));
            // Updates the value of the IS_FLUSH property of the SEQALIGN object.
            if (!$this->seqAlign->getIsFlush()) {
                // Take note that seq_count has already been decremented in the code above.
                if ($this->seqAlign->getSeqCount() <= 1) {
                    $this->seqAlign->setIsFlush(true);
                } else {
                    $samelength = TRUE;
                    $ctr = 0;
                    foreach($this->seqAlign->getSeqset() as $element) {
                        $ctr++;
                        $currlen = $element->seqlen();
                        if ($ctr == 1) {
                            $prevlen = $currlen;
                            continue;
                        }
                        if ($currlen != $prevlen) {
                            $samelength = FALSE;
                            break;
                        }
                        $prevlen = $currlen;
                    }
                    if ($samelength) {
                        $this->seqAlign->setIsFlush(true);
                    }
                }
            }
            // Return the new number of sequences in the alignment set AFTER delete operation.
            return count($this->seqAlign->getSeqset());
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }
}
