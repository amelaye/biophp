<?php
/**
 * Genbank database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Service\ParseDbAbstractManager;
use Amelaye\BioPHP\Domain\Sequence\Entity\Accession;
use Amelaye\BioPHP\Domain\Sequence\Entity\Author;
use Amelaye\BioPHP\Domain\Sequence\Entity\Keyword;
use Amelaye\BioPHP\Domain\Sequence\Entity\Reference;

/**
 * Class ParseGenbankManager
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */

final class ParseGenbankManager extends ParseDbAbstractManager
{
    /**
     * Every feature key of the INSDC Feature Table Definition (v11.4), followed by the keys it
     * deprecated on 15-DEC-2014 in favour of "regulatory" + /regulatory_class, which plasmid files
     * (SnapGene, Addgene...) still use widely. A key outside this list is skipped.
     */
    public const FEATURE_KEYS = [
        "assembly_gap", "C_region", "CDS", "centromere", "D-loop", "D_segment", "exon", "gap",
        "gene", "iDNA", "intron", "J_segment", "mat_peptide", "misc_binding", "misc_difference",
        "misc_feature", "misc_recomb", "misc_RNA", "misc_structure", "mobile_element",
        "modified_base", "mRNA", "ncRNA", "N_region", "old_sequence", "operon", "oriT",
        "polyA_site", "precursor_RNA", "prim_transcript", "primer_bind", "propeptide",
        "protein_bind", "regulatory", "repeat_region", "rep_origin", "rRNA", "S_region",
        "sig_peptide", "source", "stem_loop", "STS", "telomere", "tmRNA", "transit_peptide",
        "tRNA", "unsure", "V_region", "V_segment", "variation", "3'UTR", "5'UTR",
        // Deprecated 15-DEC-2014
        "enhancer", "promoter", "CAAT_signal", "TATA_signal", "-35_signal", "-10_signal", "RBS",
        "GC_signal", "polyA_signal", "attenuator", "terminator", "misc_signal",
    ];

    /**
     * @var \ArrayIterator|null
     */
    private ?\ArrayIterator $aLines = null;

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "GENBANK";
    }

    /**
     * Tells whether a line opens a new GenBank entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 5) == "LOCUS";
    }

    /**
     * Tells whether a line closes a GenBank entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a GenBank entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        $aLocus = preg_split("/\s+/", trim($sLine));

        return trim($aLocus[1]);
    }

    /**
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws \Exception
     */
    public function parseDataFile(array $aFlines) {
        $this->aLines = new \ArrayIterator($aFlines); // <3

        foreach($this->aLines as $lineno => $linestr) {

            switch(trim(substr($this->aLines->current(),0,12))) {
                case "LOCUS":
                    $this->parseLocus();
                    break;
                case "DEFINITION":
                    $this->parseDefinition($aFlines);
                    break;
                case "ORGANISM":
                    $this->parseOrganism($aFlines);
                    //$this->sequence->setOrganism(trim(substr($linestr,12)));
                    break;
                case "VERSION":
                    $this->parseVersion();
                    break;
                case "KEYWORDS":
                    $this->parseKeywords();
                    break;
                case "ACCESSION":
                    $this->parseAccession();
                    break;
                case "FEATURES":
                    while(1) {
                        // Verify next line. A feature key (whether we recognize it or not) and a
                        // qualifier continuation line are both indented; only an unindented line
                        // (the next top-level section, or the "//" record terminator) means the
                        // FEATURES table is over. A feature key we don't parse (e.g. "mRNA") must
                        // not stop the loop, or every feature after it in the record is lost.
                        // The end of the lines, in a record cut short, ends it as well.
                        $sNextLine = $aFlines[$this->aLines->key()+1] ?? null;
                        $bStillInFeatureTable = $sNextLine !== null && ctype_space(substr($sNextLine, 0, 1));
                        if(!$bStillInFeatureTable) {
                            break;
                        }
                        $this->aLines->next();
                        $sHead = trim(substr($this->aLines->current(), 0, 20));
                        if(in_array($sHead, self::FEATURE_KEYS, true)) {
                            $this->parseInsdcFeature($this->aLines, $aFlines, $sHead);
                        }
                    }
                    break;
                case "REFERENCE":
                    $this->parseReferences($aFlines);
                    break;
                case "SOURCE":
                    $this->sequence->setSource(trim(substr($linestr, 12)));
                    break;
                case "ORIGIN":
                    // Every line holds its position and up to six blocks of ten bases : only the
                    // bases make the sequence, not the numbers nor the spaces between the blocks.
                    $sSequence = "";
                    while (isset($aFlines[$this->aLines->key() + 1])
                        && trim(substr($aFlines[$this->aLines->key() + 1], 0, 2)) !== "//") {
                        $this->aLines->next();
                        $sSequence .= preg_replace('/[\s\d]+/', "", $this->aLines->current());
                    }
                    $this->sequence->setSequence($sSequence);
                    break;
            }
        }
    }


    /**
     * Parses a REFERENCE and its AUTHORS, CONSRTM, TITLE, JOURNAL, MEDLINE, PUBMED and REMARK
     * lines, each possibly continued on lines whose first 12 columns are blank. Lines are read
     * ahead : the iterator is left on the reference's last line, so that the section following it
     * (FEATURES, COMMENT, the next REFERENCE) is read by the main loop and not skipped.
     * The authors are separated by ", " and the last one by " and " ; a name keeps its own comma
     * and initials ("Roemer,T."). Each consortium of the CONSRTM line is kept as an author.
     * @param   array       $aFlines    The lines the script has to parse
     * @throws  \Exception
     */
    private function parseReferences(array $aFlines) {
        $oReference = new Reference();
        $aWords = preg_split("/\s+/", trim(substr($this->aLines->current(),12)));
        $oReference->setPrimAcc($this->sequence->getPrimAcc());
        $oReference->setRefno((int) $aWords[0]);
        array_shift($aWords);
        $sbaseRange = implode(" ", $aWords);
        // "(bases 1 to 3488)", or "(sites)" : the brackets go, the words stay
        $sbaseRange = trim((string) preg_replace('/^\((?:bases\s+)?|\)$/', "", $sbaseRange));
        $oReference->setBaseRange($sbaseRange);

        $aFields = [];
        $sField = null;
        while (isset($aFlines[$this->aLines->key() + 1])) {
            $sNextLine = $aFlines[$this->aLines->key() + 1];
            $sHead = trim(substr($sNextLine, 0, 12));
            if ($sHead === "" && $sField !== null && trim($sNextLine) !== "") {
                $aFields[$sField] .= " " . trim(substr($sNextLine, 12));
            } elseif (in_array($sHead, ["AUTHORS", "CONSRTM", "TITLE", "JOURNAL", "MEDLINE", "PUBMED", "REMARK"], true)) {
                $sField = $sHead;
                $aFields[$sField] = trim(substr($sNextLine, 12));
            } else {
                break;
            }
            $this->aLines->next();
        }

        $aAuthors = [];
        if (isset($aFields["AUTHORS"])) {
            // Classic style "Roemer,T., Madden,K. and Snyder,M." : the comma inside a name is
            // followed by an initial and its period, only ", " and " and " separate the authors.
            // PubMed style of RefSeq records "Sahni N, Yi S,Taipale M and Soria JM." : initials
            // carry no period, every comma separates two authors and the final period ends the list.
            $sAuthors = $aFields["AUTHORS"];
            $bPubmedStyle = !preg_match('/,[A-Z][A-Za-z]?\./', $sAuthors);
            if ($bPubmedStyle) {
                $aAuthors = preg_split('/\s*,\s*|\s+and\s+/', rtrim($sAuthors, "."));
            } else {
                $aAuthors = preg_split('/,\s+|\s+and\s+/', $sAuthors);
            }
        }
        if (isset($aFields["CONSRTM"])) {
            $aAuthors = array_merge($aAuthors, explode(";", $aFields["CONSRTM"]));
        }
        foreach ($aAuthors as $sAuthor) {
            if (trim($sAuthor) === "") {
                continue;
            }
            $oAuthor = new Author();
            $oAuthor->setPrimAcc($this->sequence->getPrimAcc());
            $oAuthor->setRefno($oReference->getRefno());
            $oAuthor->setAuthor(trim($sAuthor));
            $this->authors[] = $oAuthor;
        }

        if (isset($aFields["TITLE"])) {
            $oReference->setTitle($aFields["TITLE"]);
        }
        if (isset($aFields["JOURNAL"])) {
            $oReference->setJournal($aFields["JOURNAL"]);
        }
        if (isset($aFields["MEDLINE"])) {
            $oReference->setMedline($aFields["MEDLINE"]);
        }
        if (isset($aFields["PUBMED"])) {
            $oReference->setPubmed($aFields["PUBMED"]);
        }
        if (isset($aFields["REMARK"])) {
            $oReference->setRemark($aFields["REMARK"]);
        }
        $this->references[] = $oReference;
    }

    /**
     * Parses information about organism
     * @throws \Exception
     */
    private function parseOrganism($flines)
    {
        $organism = array();
        $organism[] = trim(substr($this->aLines->current(),12));
        while(1) {
            $sNextLine = $flines[$this->aLines->key()+1] ?? null;
            if($sNextLine === null || trim(substr($sNextLine, 0, 12)) != "") {
                break;
            }
            $this->aLines->next();
            $sLine = trim($this->aLines->current());
            $aElems = explode(";", $sLine);
            foreach($aElems as $sElem) {
                if($sElem != "") {
                    $organism[] = trim($sElem);
                }
            }
        }
        // The lineage is closed by a period, which is not part of its last rank ("Homo.").
        if (count($organism) > 1) {
            $organism[count($organism) - 1] = rtrim($organism[count($organism) - 1], ".");
        }
        $this->sequence->setOrganism($organism);
    }

    /**
     * Parses line LOCUS. NCBI writes its fields in fixed columns - name 13-28, length 30-40,
     * strandedness 45-47, molecule type 48-53, topology 56-63, division 65-67, date 69-79 - but
     * shifts them all right when the name is longer than its 16 columns (a WGS contig such as
     * NZ_JAAXYZ010000001), so they are read as words : the name, the length before its unit (bp or
     * aa), then the molecule type (absent from a protein record), topology, division and date,
     * any of which may be missing.
     * @throws      \Exception
     */
    private function parseLocus()
    {
        $aLocus = self::readLocusLine($this->aLines->current());

        $this->sequence->setPrimAcc($aLocus["name"]);
        $this->gbSequence->setPrimAcc($this->sequence->getPrimAcc());
        $this->sequence->setSeqlength($aLocus["length"]);
        $this->sequence->setMoltype($aLocus["molType"]);
        if ($aLocus["strands"] !== null) {
            $this->gbSequence->setStrands($aLocus["strands"]);
        }
        $this->gbSequence->setTopology($aLocus["topology"]);
        $this->gbSequence->setDivision($aLocus["division"]);
        $this->sequence->setDate($aLocus["date"]);
    }

    /**
     * Reads the fields of a LOCUS line, word by word (see parseLocus()). Shared with
     * ParseEntrezManager, whose records open on the same line.
     * @param   string      $sLine
     * @return  array       ["name" => string, "length" => int, "molType" => string,
     * "strands" => ?string (SINGLE, DOUBLE, MIXED), "topology" => string, "division" => string,
     * "date" => string], the last three upper-cased, "" when absent
     */
    public static function readLocusLine(string $sLine) : array
    {
        $aWords = preg_split('/\s+/', trim(substr($sLine, 12)), -1, PREG_SPLIT_NO_EMPTY);
        $aLocus = ["name" => $aWords[0] ?? "", "length" => 0, "molType" => "", "strands" => null,
            "topology" => "", "division" => "", "date" => ""];

        $iUnit = null;
        for ($i = 2; $i < count($aWords); $i++) {
            if (in_array(strtolower($aWords[$i]), ["bp", "aa"], true)) {
                $iUnit = $i;
                break;
            }
        }
        if ($iUnit === null) {
            return $aLocus;
        }
        $aLocus["length"] = (int) $aWords[$iUnit - 1];
        $aRest = array_slice($aWords, $iUnit + 1);

        if (strtolower($aWords[$iUnit]) === "bp" && isset($aRest[0]) && stripos($aRest[0], "NA") !== false) {
            $aLocus["molType"] = array_shift($aRest);
        }
        if (preg_match('/^(ss|ds|ms)-(.*)$/i', $aLocus["molType"], $aMatch)) {
            $aLocus["strands"] = ["ss" => "SINGLE", "ds" => "DOUBLE", "ms" => "MIXED"][strtolower($aMatch[1])];
            $aLocus["molType"] = $aMatch[2];
        }

        foreach ($aRest as $sWord) {
            if (in_array(strtolower($sWord), ["linear", "circular"], true)) {
                $aLocus["topology"] = strtoupper($sWord);
            } elseif (preg_match('/^\d{1,2}-[A-Za-z]{3}-\d{4}$/', $sWord)) {
                $aLocus["date"] = strtoupper($sWord);
            } elseif (preg_match('/^[A-Za-z]{3}$/', $sWord)) {
                $aLocus["division"] = strtoupper($sWord);
            }
        }

        return $aLocus;
    }


    /**
     * Parses DEFINITION field
     * @ param      array            $flines
     * @throws      \Exception
     */
    private function parseDefinition($flines)
    {
        $wordarray = explode(" ", $this->aLines->current());
        array_shift($wordarray);
        $sDefinition = trim(implode(" ", $wordarray));
        while(1) {
            $sNextLine = $flines[$this->aLines->key()+1] ?? null;
            if($sNextLine === null || trim(substr($sNextLine, 0, 12)) != "") {
                break;
            }
            $this->aLines->next();
            $sDefinition .= " ".trim($this->aLines->current());
        }
        $this->sequence->setDescription($sDefinition);
    }


    /**
     * Parses VERSION field
     * @throws      \Exception
     */
    private function parseVersion()
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        $this->gbSequence->setVersion($wordarray[1]);
        if (count($wordarray) == 3) {
            $this->gbSequence->setNcbiGiId($wordarray[2]);
        }
    }


    /**
     * Parses KEYWORDS field, possibly continued on lines whose first 12 columns are blank : the
     * keywords are separated by ";" and the field closed by a period, alone when there is none.
     * Format : KEYWORDS    RefSeq; MANE Select.
     * @throws      \Exception
     */
    private function parseKeywords()
    {
        $sKeywords = rtrim(trim(implode(" ", $this->readContinuedField())), ".");
        foreach (preg_split("/;+/", $sKeywords) as $sWord) {
            $sWord = trim($sWord);
            if ($sWord === "") {
                continue;
            }
            $oKeyword = new Keyword();
            $oKeyword->setPrimAcc($this->sequence->getPrimAcc());
            $oKeyword->setKeywords($sWord);
            $this->keywords[] = $oKeyword;
        }
    }


    /**
     * Parses ACCESSION field : the primary accession, then the secondary ones, possibly continued
     * on lines whose first 12 columns are blank. A record cut out of a larger one (a CON or
     * contig sub-range) follows them with "REGION: from..to", which is not an accession.
     * Format : ACCESSION   NC_000913 REGION: 1..1000
     * @throws      \Exception
     */
    private function parseAccession()
    {
        $sAccessions = preg_replace('/\bREGION:\s*\S+/', "", implode(" ", $this->readContinuedField()));
        $wordarray = preg_split("/\s+/", trim($sAccessions), -1, PREG_SPLIT_NO_EMPTY);
        $this->sequence->setPrimAcc($wordarray[0] ?? "");
        array_shift($wordarray);
        foreach($wordarray as $word) {
            $oAccession = new Accession();
            $oAccession->setPrimAcc($this->sequence->getPrimAcc());
            $oAccession->setAccession($word);
            $this->accession[] = $oAccession;
        }
    }

    /**
     * Returns the data of the current line, past its 12 label columns, and of the lines continuing
     * it (first 12 columns blank), leaving the iterator on the last of them.
     * @return  string[]
     */
    private function readContinuedField() : array
    {
        $aFlines = $this->aLines->getArrayCopy();
        $aData = [trim(substr($this->aLines->current(), 12))];
        while (true) {
            $sNextLine = $aFlines[$this->aLines->key() + 1] ?? null;
            if ($sNextLine === null || trim($sNextLine) === "" || trim(substr($sNextLine, 0, 12)) !== "") {
                break;
            }
            $this->aLines->next();
            $aData[] = trim(substr($this->aLines->current(), 12));
        }
        return $aData;
    }
}
