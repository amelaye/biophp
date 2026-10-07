<?php
/**
 * Genbank database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 7 October 2026
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
                        $sNextLine = $aFlines[$this->aLines->key()+1] ?? "";
                        $bStillInFeatureTable = ($sNextLine === "") || ctype_space(substr($sNextLine, 0, 1));
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
        $sbaseRange = str_replace(["(bases ",")"], "", $sbaseRange);
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
            $head = trim(substr($flines[$this->aLines->key()+1],0, 12));
            if($head != "") {
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
        $this->sequence->setOrganism($organism);
    }

    /**
     * Parses line LOCUS
     * @return      mixed
     * @throws      \Exception
     */
    private function parseLocus()
    {
        $this->sequence->setPrimAcc(trim(substr($this->aLines->current(), 12, 16)));
        $this->gbSequence->setPrimAcc($this->sequence->getPrimAcc());

        $this->sequence->setSeqlength(trim(substr($this->aLines->current(), 29, 11)) * 1);
        $this->sequence->setMoltype(trim(substr($this->aLines->current(), 47, 6)));

        switch(substr($this->aLines->current(), 44, 3)) {
            case "ss-":
                $this->gbSequence->setStrands("SINGLE");
                break;
            case "ds-":
                $this->gbSequence->setStrands("DOUBLE");
                break;
            case "ms-":
                $this->gbSequence->setStrands("MIXED");
                break;
        }

        $this->gbSequence->setTopology(strtoupper(trim(substr($this->aLines->current(), 55, 8))));
        $this->gbSequence->setDivision(strtoupper(trim(substr($this->aLines->current(), 64, 3))));
        $this->sequence->setDate(strtoupper(trim(substr($this->aLines->current(), 68, 11))));
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
            $head = trim(substr($flines[$this->aLines->key()+1],0, 12));
            if($head != "") {
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
     * Parses KEYWORDS field
     * @throws      \Exception
     */
    private function parseKeywords()
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        array_shift($wordarray);
        $wordarray = preg_split("/;+/", implode(" ", $wordarray));
        if ($wordarray[0] != ".") {
            foreach($wordarray as $word) {
                $oKeyword = new Keyword();
                $oKeyword->setPrimAcc($this->sequence->getPrimAcc());
                $oKeyword->setKeywords($word);
                $this->keywords[] = $oKeyword;
            }
        }
    }


    /**
     * Parses ACCESSION field
     * @throws      \Exception
     */
    private function parseAccession()
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        $this->sequence->setPrimAcc($wordarray[1]);
        array_shift($wordarray);
        array_shift($wordarray);
        foreach($wordarray as $word) {
            $oAccession = new Accession();
            $oAccession->setPrimAcc($this->sequence->getPrimAcc());
            $oAccession->setAccession($word);
            $this->accession[] = $oAccession;
        }
    }
}
