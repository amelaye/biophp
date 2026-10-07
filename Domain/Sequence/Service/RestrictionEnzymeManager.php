<?php
/**
 * Enzyme restriction manager
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\Interfaces\TypeIIEndonucleaseApiAdapter;
use Amelaye\BioPHP\Domain\Sequence\Entity\Enzyme;
use Amelaye\BioPHP\Domain\Sequence\Interfaces\RestrictionEnzymeInterface;
use Amelaye\BioPHP\Domain\Sequence\Interfaces\SequenceInterface;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;

/**
 * Class RestrictionEnzymeManager - substances that can "cut" a DNA strand
 * into two or more fragments along special sites called restriction sites. They
 * are an important tool in recombinant DNA technology.
 * @package Amelaye\BioPHP\Domain\Sequence\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class RestrictionEnzymeManager implements RestrictionEnzymeInterface
{
    /**
     * @var array
     */
    private array $aRestEnzimDB;

    /**
     * @var Enzyme
     */
    private ?Enzyme $enzyme = null;

    /**
     * @var SequenceInterface|null
     */
    private ?SequenceInterface $sequenceManager = null;

    /**
     * True when the current enzyme was described by parseEnzyme(..., "custom") rather than read from
     * the reference data
     * @var bool
     */
    private bool $bCustomEnzyme = false;

    /**
     * RestrictionEnzymeManager constructor.
     * @param Enzyme                        $oEnzyme
     * @param TypeIIEndonucleaseApiAdapter  $typeIIEndonucleaseApi
     */
    public function __construct(TypeIIEndonucleaseApiAdapter $typeIIEndonucleaseApi, Enzyme $oEnzyme)
    {
        $aEnzymes           = $typeIIEndonucleaseApi->getTypeIIEndonucleases();
        $this->aRestEnzimDB = $typeIIEndonucleaseApi::GetTypeIIbEndonucleasesCleavagePosUpper($aEnzymes);
        $this->enzyme       = $oEnzyme;

    }

    /**
     * Sets a new enzyme element
     */
    public function setEnzyme()
    {
        $this->enzyme = new Enzyme();
    }

    /**
     * @return Enzyme
     */
    public function getEnzyme() : Enzyme
    {
        return $this->enzyme;
    }

    /**
     * Sets a sequence object
     * @param SequenceInterface $sequenceManager
     */
    public function setSequenceManager(SequenceInterface $sequenceManager) {
        $this->sequenceManager = $sequenceManager;
    }

    /**
     * It creates a new Enzyme object and initializes its properties accordingly.
     * If passed with make = 'custom', object will be added to aRestEnzimDB.
     * If not, the function will attemp to retrieve data from aRestEnzimDB.
     * If unsuccessful in retrieving data, it will return an error flag.
     * An enzyme is described here by its upper-strand cut only. A reference entry places its two cuts
     * symmetrically within its site, so its site read on the other strand is cut at the same offset.
     * A custom enzyme may not (a Type IIS one such as BsaI cuts outside an asymmetric site), and
     * nothing tells where its lower-strand cut lies : its site is only searched as written.
     * @param   string      $sName
     * @param   string      $sPattern
     * @param   string      $sCutpos
     * @param   string      $sMake
     * @throws  \Exception
     */
    public function parseEnzyme(string $sName, ?string $sPattern = null, ?string $sCutpos = null, string $sMake = "custom")
    {
        $this->bCustomEnzyme = ($sMake == "custom");
        if ($sMake == "custom") {
            $iCutpos = (int) $sCutpos;
            $this->enzyme->setName($sName);
            $this->enzyme->setPattern($sPattern);
            $this->enzyme->setCutpos($iCutpos);
            $this->enzyme->setLength($this->patternLength($this->enzyme->getPattern()));

            $inner = array();
            $inner[] = $sPattern;
            $inner[] = $iCutpos;
            $this->aRestEnzimDB[$this->enzyme->getName()] = $inner;
        } else {
            // Look for given endonuclease in the aRestEnzimDB array.
            $this->enzyme->setName($sName);
            $temp = $this->getPattern($this->enzyme->getName());
            if (!$temp) {
                throw new \Exception("Cannot find entry in restriction endonuclease database.");
            } else {
                $this->enzyme->setPattern($temp);
                $this->enzyme->setCutpos($this->getCutPos($this->enzyme->getName()));
                $this->enzyme->setLength($this->patternLength($this->enzyme->getPattern()));
            }
        }
    }

    /**
     * Cuts a DNA sequence into fragments using the restriction enzyme object.
     * Sites are searched on both strands, a degenerate (IUPAC) site is matched by every sequence it
     * stands for, and each upper-strand cut position splits the sequence once, however many sites
     * or alternatives lead to it.
     * @param   string             $options            May be "N" or "O".  If "N", sites overlapping a
     * site already found are ignored. If "O", overlapping sites are cut as well. If omitted, this
     * defaults to "N".
     * @return  array       The fragments, in sequence order : the whole sequence when it holds no site
     * @throws  \InvalidArgumentException  When $options is neither "N" nor "O"
     */
    public function cutSeq(string $options = "N") : array
    {
        if ($options !== "N" && $options !== "O") {
            throw new \InvalidArgumentException(sprintf('cutSeq() option must be "N" or "O", "%s" given.', $options));
        }

        $sSequence = $this->sequenceManager->getSequence()->getSequence();
        $aFragments = [];
        $iStart = 0;
        foreach ($this->findCutPositions($sSequence, $options === "O") as $iCut) {
            $aFragments[] = substr($sSequence, $iStart, $iCut - $iStart);
            $iStart = $iCut;
        }
        // The last (right-most) fragment, or the whole sequence when the enzyme does not cut it.
        $aFragments[] = substr($sSequence, $iStart);
        return $aFragments;
    }

    /**
     * Returns the pattern associated with a given restriction endonuclease.
     * @param   string      $RestEn_Name
     * @return  string      The sequence pattern (string) recognized by the given restriction enzyme.
     */
    public function getPattern(string $RestEn_Name) : string
    {
        return $this->aRestEnzimDB[$RestEn_Name][0];
    }

    /**
     * Returns the cutting position of the restriction enzyme object.
     * @param   string      $RestEn_Name
     * @return  int         Returns the cutting position (an integer) of the restriction enzyme object.
     */
    public function getCutPos(string $RestEn_Name) : int
    {
        return $this->aRestEnzimDB[$RestEn_Name][1];
    }

    /**
     * Returns the length of the cutting pattern of the restriction enzyme object.
     * @param   string  $RestEn_Name
     * @return  int     The length (integer) of the restriction pattern recognized by the enzyme.
     */
    public function getLength(string $RestEn_Name = "") : int
    {
        if ($RestEn_Name == "") {
            return $this->patternLength($this->enzyme->getPattern());
        } else {
            return $this->patternLength($this->aRestEnzimDB[$RestEn_Name][0]);
        }
    }

    /**
     * A powerful method for searching our database of endonucleases for a particular
     * restriction enzyme exhibiting certain properties like pattern, cutting position,
     * and length, or combinations thereof.
     * 5 Cases: pattern only, cutpos only, patternlength only, pattern and cutpos, cutpos and patternlength
     * @param   string      $sPattern    The pattern of the restriction enzyme we wish to look for.
     * @param   int         $iCutpos     The cutting position of the restriction enzyme we wish to look for.
     * @param   int         $iPlen       The length of the restriction enzyme we wish to look for.
     * @return  array       A list of restriction enyzmes that meet the criteria specified by the $pattern, $cutpos,
     * and $plen parameters.
     * @throws  \Exception
     */
    public function findRestEn(?string $sPattern = null, ?int $iCutpos = null, ?int $iPlen = null) : array
    {
        // Case 1: Pattern only
        if (!is_null($sPattern) && is_null($iCutpos) && is_null($iPlen)) {
            $aEnzymes = $this->fetchPatternOnly($sPattern);
            return $aEnzymes;
        }

        // Case 2: Cutpos only
        if (is_null($sPattern) && !is_null($iCutpos) && is_null($iPlen)) {
            $aEnzymes = $this->fetchCutpos($iCutpos);
            return $aEnzymes;
        } 

        // Case 3: Patternlength only
        if (is_null($sPattern) && is_null($iCutpos) && !is_null($iPlen)) {
            $aEnzymes = $this->fetchLength($iPlen);
            return $aEnzymes;
        }

        // Case 4: Pattern and cutpos only
        if (!is_null($sPattern) && !is_null($iCutpos) && is_null($iPlen)) {
            $aEnzymes = $this->fetchPatternAndCutpos($sPattern, $iCutpos);
            return $aEnzymes;
        }

        // Case 5: Cutpos and plen only.
        if (is_null($sPattern) && !is_null($iCutpos) && !is_null($iPlen)) {
            $aEnzymes = $this->fetchCutposAndPlen($iCutpos, $iPlen);
            return $aEnzymes;
        }

        throw new \Exception("Invalid combination of function parameters.");
    }

    /**
     * Returns every site an enzyme pattern stands for, upper-cased : each alternative of a pattern
     * written "SITE1 or SITE2" (AciI, BbvCI, BssSI), and the reverse complement of each, since the
     * enzyme binds double-stranded DNA and a non-palindromic site (AccBSI, CCGCTC) also lies on the
     * other strand (GAGCGG). A palindromic site is its own reverse complement and is listed once.
     * @param   string      $sPattern
     * @param   bool        $bBothStrands   False to list the sites as written only
     * @return  string[]
     */
    private function sitesOf(string $sPattern, bool $bBothStrands = true) : array
    {
        $aSites = [];
        foreach (preg_split('/\s+or\s+/i', trim($sPattern)) as $sSite) {
            $oSite = new DnaSequence(strtoupper(trim($sSite)));
            $aSites[] = $oSite->getValue();
            if ($bBothStrands) {
                $aSites[] = $oSite->reverseComplement()->getValue();
            }
        }
        return array_values(array_unique($aSites));
    }

    /**
     * Returns the length of the site a pattern stands for, the alternatives of a "SITE1 or SITE2"
     * pattern having the same length.
     * @param   string      $sPattern
     * @return  int
     */
    private function patternLength(string $sPattern) : int
    {
        return strlen(trim(preg_split('/\s+or\s+/i', trim($sPattern))[0]));
    }

    /**
     * Returns the zero-based positions, in ascending order and each listed once, at which the
     * enzyme cuts the upper strand of a linear sequence. A site found on the other strand is cut at
     * the same offset from its start : every Type II entry places its two cuts symmetrically within
     * its site (length = 2 x upper cut + lower cut offset), so the upper-strand cut of a reversed
     * site stays at that offset. A custom enzyme is only searched on the strand its site is written
     * for (see parseEnzyme()). A cut at either end of the sequence splits nothing and is dropped.
     * @param   string      $sSequence
     * @param   bool        $bOverlapping   True to also find sites overlapping one another
     * @return  int[]
     */
    private function findCutPositions(string $sSequence, bool $bOverlapping) : array
    {
        $aExpandedSites = array_map(function (string $sSite) {
            return $this->sequenceManager->expandNa($sSite);
        }, $this->sitesOf($this->enzyme->getPattern(), !$this->bCustomEnzyme));

        $sRegex = '(' . implode('|', $aExpandedSites) . ')';
        if ($bOverlapping) {
            $sRegex = '(?=' . $sRegex . ')';
        }
        preg_match_all('/' . $sRegex . '/i', $sSequence, $aMatches, PREG_OFFSET_CAPTURE);

        $iLength = strlen($sSequence);
        $aCutPositions = [];
        foreach ($aMatches[0] as $aMatch) {
            $iCut = $aMatch[1] + $this->enzyme->getCutpos();
            if ($iCut > 0 && $iCut < $iLength) {
                $aCutPositions[$iCut] = $iCut;
            }
        }
        sort($aCutPositions, SORT_NUMERIC);
        return $aCutPositions;
    }

    /**
     * @param   string      $sPattern
     * @return  array
     */
    private function fetchPatternOnly(string $sPattern) : array {
        $aEnzymes = [];
        foreach($this->aRestEnzimDB as $sName => $aEnzyme) {
            if (in_array(strtoupper($sPattern), $this->sitesOf($aEnzyme[0]), true)) {
                $aEnzymes[] = $sName;
            }
        }
        return $aEnzymes;
    }

    /**
     * @param   string      $sPattern
     * @param   int         $iCutpos
     * @return  array
     */
    private function fetchPatternAndCutpos(string $sPattern, int $iCutpos) : array {
        $aEnzymes = [];
        foreach($this->aRestEnzimDB as $sName => $aEnzyme) {
            if (in_array(strtoupper($sPattern), $this->sitesOf($aEnzyme[0]), true) && ($aEnzyme[1] == $iCutpos)) {
                $aEnzymes[] = $sName;
            }
        }
        return $aEnzymes;
    }

    /**
     * @param   string | int     $sCutpos
     * @return  array
     * @throws  \Exception
     */
    private function fetchCutpos($sCutpos) : array {
        $aEnzymes = [];
        if (is_string($sCutpos)) {
            if (preg_match("/^<\d+$/", $sCutpos)) {
                foreach($this->aRestEnzimDB as $sName => $aEnzyme) {
                    if ($aEnzyme[1] < (int) substr($sCutpos,1)) {
                        $aEnzymes[] = $sName;
                    }
                }
            } elseif (preg_match("/^>\d+$/", $sCutpos)) {
                foreach($this->aRestEnzimDB as $sName => $aEnzyme) {
                    if ($aEnzyme[1] > (int) substr($sCutpos,1)) {
                        $aEnzymes[] = $sName;
                    }
                }
            } elseif (preg_match("/^>=\d+$/", $sCutpos)) {
                foreach($this->aRestEnzimDB as $sName => $aEnzyme) {
                    if ($aEnzyme[1] >= (int) substr($sCutpos,2)) {
                        $aEnzymes[] = $sName;
                    }
                }
            } elseif (preg_match("/^<=\d+$/", $sCutpos)) {
                foreach($this->aRestEnzimDB as $sName => $aEnzyme) {
                    if ($aEnzyme[1] <= (int) substr($sCutpos,2)) {
                        $aEnzymes[] = $sName;
                    }
                }
            } elseif (preg_match("/^=\d+$/", $sCutpos)) {
                foreach($this->aRestEnzimDB as $sName => $aEnzyme) {
                    if ($aEnzyme[1] == substr($sCutpos,1)) {
                        $aEnzymes[] = $sName;
                    }
                }
            } else {
                throw new \Exception("Malformed cutpos parameter.");
            }
        } elseif (is_int($sCutpos)) {
            foreach($this->aRestEnzimDB as $sName => $aEnzyme) {
                if ($aEnzyme[1] == $sCutpos) {
                    $aEnzymes[] = $sName;
                }
            }
        }
        return $aEnzymes;
    }

    /**
     * @param  int    $iPlen
     * @return array
     */
    private function fetchLength(int $iPlen) : array {
        $aEnzymes = [];
        foreach($this->aRestEnzimDB as $sName => $aEnzyme) {
            if ($this->patternLength($aEnzyme[0]) == $iPlen) {
                $aEnzymes[] = $sName;
            }
        }
        return $aEnzymes;
    }

    /**
     * @param   int     $iCutpos
     * @param   int     $iPlen
     * @return  array
     */
    private function fetchCutposAndPlen(int $iCutpos, int $iPlen) : array {
        $aEnzymes = [];
        foreach($this->aRestEnzimDB as $sName => $aEnzyme) {
            if (($aEnzyme[1] == $iCutpos) && ($this->patternLength($aEnzyme[0]) == $iPlen)) {
                $aEnzymes[] = $sName;
            }
        }
        return $aEnzymes;
    }
}
