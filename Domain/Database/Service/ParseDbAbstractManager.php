<?php
/**
 * Global database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;
use Amelaye\BioPHP\Domain\Sequence\Entity\Feature;
use Amelaye\BioPHP\Domain\Sequence\Entity\GbSequence;
use Amelaye\BioPHP\Domain\Sequence\Traits\FormatsTrait;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Amelaye\BioPHP\Domain\Sequence\Entity\SrcForm;

/**
 * Class ParseDbAbstractManager
 * @package Amelaye\BioPHP\Domain\Database\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class ParseDbAbstractManager implements ParseDatabaseInterface
{
    use FormatsTrait;

    /**
     * @var array
     */
    protected ?array $accession = null;

    /**
     * @var Sequence
     */
    protected ?Sequence $sequence = null;

    /**
     * @var array
     */
    protected ?array $authors = null;

    /**
     * @var array
     */
    protected ?array $features = null;

    /**
     * @var array
     */
    protected ?array $keywords = null;

    /**
     * @var array
     */
    protected ?array $references = null;

    /**
     * @var SrcForm
     */
    protected ?SrcForm $srcForm = null;

    /**
     * @var GbSequence
     */
    protected ?GbSequence $gbSequence = null;

    /**
     * @var array
     */
    protected ?array $spDatabank = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->accession    = []; // array of Accessions();
        $this->sequence     = new Sequence();
        $this->authors      = []; // array of Authors()
        $this->gbSequence   = new GbSequence();
        $this->features     = []; // array of Features();
        $this->keywords     = []; // array of Keywords();
        $this->references   = []; // array of References();
        $this->srcForm      = new SrcForm();
        $this->spDatabank   = []; // array of SpDatabank();
    }

    /**
     * @return array
     */
    public function getAccession() : array {
        return $this->accession;
    }

    /**
     * @return Sequence
     */
    public function getSequence() : Sequence {
        return $this->sequence;
    }

    /**
     * @return array
     */
    public function getAuthors() : array {
        return $this->authors;
    }

    /**
     * @return GbSequence
     */
    public function getGbSequence() : GbSequence {
        return $this->gbSequence;
    }

    /**
     * @return array
     */
    public function getFeatures() : array {
        return $this->features;
    }

    /**
     * @return array
     */
    public function getKeywords() : array {
        return $this->keywords;
    }

    /**
     * @return array
     */
    public function getReferences() : array {
        return $this->references;
    }

    /**
     * @return SrcForm
     */
    public function getSrcForm() : SrcForm {
        return $this->srcForm;
    }

    /**
     * @return array
     */
    public function getSpDatabank(): array
    {
        return $this->spDatabank;
    }

    /**
     * @param array $accession
     */
    public function setAccession(array $accession): void
    {
        $this->accession = $accession;
    }

    /**
     * @param Sequence $sequence
     */
    public function setSequence(Sequence $sequence): void
    {
        $this->sequence = $sequence;
    }

    /**
     * @param array $authors
     */
    public function setAuthors(array $authors): void
    {
        $this->authors = $authors;
    }

    /**
     * @param array $features
     */
    public function setFeatures(array $features): void
    {
        $this->features = $features;
    }

    /**
     * @param array $keywords
     */
    public function setKeywords(array $keywords): void
    {
        $this->keywords = $keywords;
    }

    /**
     * @param array $references
     */
    public function setReferences(array $references): void
    {
        $this->references = $references;
    }

    /**
     * @param SrcForm $srcForm
     */
    public function setSrcForm(SrcForm $srcForm): void
    {
        $this->srcForm = $srcForm;
    }

    /**
     * @param GbSequence $gbSequence
     */
    public function setGbSequence(GbSequence $gbSequence): void
    {
        $this->gbSequence = $gbSequence;
    }

    /**
     * @param array $spDatabank
     */
    public function setSpDatabank(array $spDatabank): void
    {
        $this->spDatabank = $spDatabank;
    }

    /**
     * Parses an INSDC feature location (shared by GenBank and EMBL) into its outer bounds and
     * strand, 1-based and inclusive. The location as written is kept apart (Feature::getFtLocation()) :
     * these bounds only frame it.
     * - complement(), join() and order() wrappers and the "<" / ">" partial marks are stripped ;
     * - "a..b" spans a to b, "a" is one base, "a.b" one base somewhere in a..b and "a^b" the site
     *   between two bases : both give a..b ;
     * - a segment of another entry ("J00194.1:100..202") lies on another sequence and is left out ;
     *   a location made of such segments only has no bounds here (null, null) ;
     * - the segments of a join() are listed in the order they are transcribed. When the record is
     *   circular, all of them lie on the same strand and that order goes back past the origin
     *   (join(4900..5000,1..100), or join(complement(1..100),complement(4900..5000)) on the other
     *   strand), that order goes back exactly once, from the last base of the molecule to the first,
     *   and the join is not an order(), the bounds are from = 4900, to = 100 : from > to, the
     *   origin-crossing convention of PlasmidFeature, as for a single segment written "4900..100".
     *   Otherwise - a linear record, an order(), a trans-spliced gene (plant organelle nad1, rps12)
     *   whose segments are listed out of order - they are the lowest start and the highest end.
     * @param   string  $sLocation  The raw location text, e.g. "complement(join(<1..10,50..>60))".
     * @return  array   [$iFrom, $iTo, $sStrand] - $sStrand is "-" when every segment of this entry
     * is complemented, "+" when none is, null when the location lies on both strands (a trans-spliced
     * join(complement(a..b),c..d)).
     */
    protected function parseLocationBounds(string $sLocation) : array
    {
        $sStrand = (strpos($sLocation, "complement(") !== false) ? "-" : "+";
        $bOuterComplement = (bool) preg_match('/^\s*complement\(\s*(join|order)\(/', $sLocation);

        $aSegments = [];
        foreach (explode(",", preg_replace('/\s+/', "", $sLocation)) as $sRawSegment) {
            $bComplement = strpos($sRawSegment, "complement(") !== false;
            // An uncertain position "(102.110)" is one base within a range : the start of the
            // segment takes the first of them, its end the last, so the span is never shorter than
            // what the record says. They go before the parentheses of join() and complement().
            $sSegment = preg_replace('/\((\d+)\.(\d+)\)(?=\.\.)/', '$1', $sRawSegment);
            $sSegment = preg_replace('/(?<=\.\.)\((\d+)\.(\d+)\)/', '$2', $sSegment);
            $sSegment = str_replace(["complement(", "join(", "order(", ")", "<", ">"], "", $sSegment);
            if ($sSegment === "" || strpos($sSegment, ":") !== false) {
                continue;
            }
            if (!preg_match('/^(\d+)(?:(?:\.\.|\.|\^)(\d+))?$/', $sSegment, $aMatch)) {
                continue;
            }
            $iStart = (int) $aMatch[1];
            $iEnd = isset($aMatch[2]) ? (int) $aMatch[2] : $iStart;
            // Kept as written : a single "4900..100" segment already crosses the origin.
            $aSegments[] = [$iStart, $iEnd, $bComplement || $bOuterComplement];
        }

        if ($aSegments === []) {
            // Nothing of this entry : a complement() around another entry's segment says nothing here
            return [null, null, strpos($sLocation, ":") !== false ? "+" : $sStrand];
        }

        $aStrands = array_unique(array_column($aSegments, 2));
        if (count($aStrands) > 1) {
            $sStrand = null;
        } else {
            // Read from this entry's segments only : a complement() around a remote segment says
            // nothing about this sequence.
            $sStrand = $aSegments[0][2] ? "-" : "+";
        }
        $bCircular = $this->gbSequence !== null && $this->gbSequence->getTopology() === "CIRCULAR";
        // order() implies no order of the segments : listing them backwards crosses nothing
        $bOrdered = preg_match('/\border\(/', $sLocation) !== 1;
        if ($bCircular && $bOrdered && count($aSegments) > 1 && count($aStrands) === 1) {
            // join(complement(c..d),complement(a..b)) is transcribed from the last base of the
            // feature : listed from its 3' end, read back to front it runs like a direct one.
            if ($aSegments[0][2] && !$bOuterComplement) {
                $aSegments = array_reverse($aSegments);
            }
            // The feature crosses the origin when its segments go back once, from the last base of the
            // molecule to the first : a trans-spliced gene (plant organelle nad1, rps12) lists its
            // segments out of order too, several times, and not from end to start
            $aDescents = [];
            for ($i = 1; $i < count($aSegments); $i++) {
                if ($aSegments[$i][0] < $aSegments[$i - 1][0]) {
                    $aDescents[] = $i;
                }
            }
            if (count($aDescents) === 1) {
                $i = $aDescents[0];
                $iLength = $this->sequence !== null ? (int) $this->sequence->getSeqLength() : 0;
                if ($iLength <= 0 || ($aSegments[$i - 1][1] === $iLength && $aSegments[$i][0] === 1)) {
                    return [$aSegments[0][0], $aSegments[count($aSegments) - 1][1], $sStrand];
                }
            }
        }

        if (count($aSegments) === 1) {
            return [$aSegments[0][0], $aSegments[0][1], $sStrand];
        }

        return [min(array_column($aSegments, 0)), max(array_column($aSegments, 1)), $sStrand];
    }

    /**
     * Parses one feature of an INSDC feature table, shared by GenBank and EMBL, whose columns are
     * the same : the key from column 6, the location (possibly wrapped over several lines) and the
     * /qualifier lines from column 22. $aFlines is read ahead to find where the feature ends : a
     * line holding something in its first 12 columns starts another feature or section, so EMBL
     * hands its lines with their "FT" tag blanked out.
     * @param   \ArrayIterator  $oLines     The parser's line iterator, on the feature's key line
     * @param   array           $aFlines    The lines, as read ahead
     * @param   string          $sKey       The feature key
     * @throws  \Exception
     */
    protected function parseInsdcFeature(\ArrayIterator $oLines, array $aFlines, string $sKey) {
        $sLocation = trim(substr($oLines->current(), 20));
        // A location can wrap across several physical lines (a spliced join() feature commonly
        // does). Keep appending lines to it until the next one starts a qualifier ("/...") or a
        // new feature/section begins.
        while (true) {
            $sNextLine = $aFlines[$oLines->key() + 1] ?? "";
            $sNextTrimmed = trim($sNextLine);
            if ($sNextTrimmed === "" || $sNextTrimmed[0] === "/") {
                break;
            }
            if (trim(substr($sNextLine, 0, 12)) != "") {
                break;
            }
            $oLines->next();
            $sLocation .= trim(substr($oLines->current(), 20));
        }
        $aBounds = $this->parseLocationBounds($sLocation);
        $aBounds[] = $sLocation;
        // A feature with no qualifier at all is directly followed by the next feature key or
        // section : that line must not be consumed as if it were this feature's qualifier.
        $sNextLine = $aFlines[$oLines->key() + 1] ?? "";
        if (trim($sNextLine) === "" || trim(substr($sNextLine, 0, 12)) !== "") {
            $this->buildInsdcFeature("", $sKey, $aBounds);
            return;
        }
        $oLines->next();
        $sLine = trim(substr($oLines->current(), 20));
        while (1) {
            // Decide from the *next* line, before consuming it: a new "/qualifier=" line means
            // the one just accumulated in $sLine is complete. A new feature key (or a top-level
            // section like ORIGIN) occupies columns 0-11, same as the check that opens a
            // feature's own key/location line; a qualifier's own wrapped continuation line never
            // does, since its content starts only past column 20. Checking this on the line
            // about to be consumed - not one line later, once it has already been swallowed - is
            // what keeps the next feature's key/location line from being absorbed as if it were
            // more of this feature's qualifier text.
            $sNextLine = $aFlines[$oLines->key()+1] ?? "";
            $sNextTrimmed = trim($sNextLine);
            // Inside a quoted value (an odd number of quotes so far, an escaped "" counting two), a
            // wrapped line starting with "/" is more of the value, not a new qualifier.
            $bInsideQuotedValue = substr_count($sLine, '"') % 2 === 1;
            $bNextStartsQualifier = !$bInsideQuotedValue
                && ($sNextTrimmed !== "") && ($sNextTrimmed[0] === "/");
            // The end of the lines ends the feature as well.
            $bNextIsNewFeatureOrSection = trim(substr($sNextLine, 0, 12)) !== ""
                || !array_key_exists($oLines->key() + 1, $aFlines);

            if ($bNextStartsQualifier || $bNextIsNewFeatureOrSection) {
                $this->buildInsdcFeature($sLine, $sKey, $aBounds);
                $sLine = ""; // RAZ
            }
            if ($bNextIsNewFeatureOrSection) {
                break;
            }
            $oLines->next();
            $sLine .= " ".trim(substr($oLines->current(), 20));
        }
    }

    /**
     * Creates Feature object
     * @param   string  $sLine
     * @param   string  $sKey
     * @param   array   $aBounds    [$iFtFrom, $iFtTo, $sStrand], as returned by
     * parseLocationBounds(), followed by the location as written.
     */
    private function buildInsdcFeature(string $sLine, string $sKey, array $aBounds) {
        // Only the qualifier's own leading "/" and the value's enclosing quotes are syntax : a "/"
        // or "=" inside the value is data (e.g. /note="5'/3' ends; Km=2 mM"), and a doubled ""
        // inside a quoted value is an escaped quote. A flag qualifier (/pseudo) has no value, and a
        // feature with no qualifier at all still gets one row, with an empty qualifier, so its key
        // and location are not lost.
        [$sQualifier, $sValue] = explode("=", ltrim(trim($sLine), "/"), 2) + [1 => ""];
        if (strlen($sValue) >= 2 && $sValue[0] === '"' && substr($sValue, -1) === '"') {
            $sValue = str_replace('""', '"', substr($sValue, 1, -1));
        }
        // Wrapped lines are joined with a space, right for free text but not for a protein
        // sequence, which must not gain a space at every line break.
        if ($sQualifier === "translation") {
            $sValue = (string) preg_replace('/\s+/', "", $sValue);
        }
        $oFeature = new Feature();
        $oFeature->setPrimAcc($this->sequence->getPrimAcc());
        $oFeature->setFtKey($sKey);
        $oFeature->setFtQual($sQualifier);
        $oFeature->setFtValue($sValue);
        $oFeature->setFtFrom($aBounds[0]);
        $oFeature->setFtTo($aBounds[1]);
        $oFeature->setStrand($aBounds[2] ?? null);
        $oFeature->setFtLocation($aBounds[3] ?? null);
        $this->features[] = $oFeature;
    }

    /**
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws \Exception
     */
    public function parseDataFile(array $aFlines) {}
}