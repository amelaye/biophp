<?php
/**
 * PDB (Protein Data Bank) database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 8 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;
use Amelaye\BioPHP\Domain\Parser\Entity\PdbAtom;
use Amelaye\BioPHP\Domain\Parser\Entity\PdbHelix;
use Amelaye\BioPHP\Domain\Parser\Entity\PdbSheet;
use Amelaye\BioPHP\Domain\Parser\Interfaces\PdbAtomInterface;
use Amelaye\BioPHP\Domain\Parser\Interfaces\PdbHelixInterface;
use Amelaye\BioPHP\Domain\Parser\Interfaces\PdbSheetInterface;

/**
 * Class ParsePdbManager
 * PDB structure files describe 3D atomic coordinates, not GenBank/EMBL-style
 * annotated sequences, so this parser does not reuse the Sequence/Feature entities
 * of ParseDbAbstractManager - it exposes its own plain Domain\Parser\Entity objects instead.
 * Only the fields most commonly used in structural bioinformatics are covered
 * (identification, sequence per chain, secondary structure, atomic coordinates);
 * the many rarely-used PDB record types (CONECT, ANISOU, MASTER, ...) are left out,
 * matching what Legacy/pdb.inc.php itself had actually implemented.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParsePdbManager implements ParseDatabaseInterface
{
    /**
     * Residue name to one-letter code table, for turning SEQRES residues into a usable sequence.
     * Besides the twenty amino acids : selenocysteine (SEC, U) and pyrrolysine (PYL, O), the
     * ambiguous ASX (B) and GLX (Z), selenomethionine (MSE), the methionine substitute of
     * SAD/MAD-phased structures, read as its parent M, and the nucleotides of a DNA (DA, DC, DG,
     * DT) or RNA (A, C, G, U) chain. Other residues (a modified one, a ligand) map to "X".
     * @var array
     */
    private static $aminoAcidCodes = [
        "ALA" => "A", "ARG" => "R", "ASN" => "N", "ASP" => "D", "CYS" => "C",
        "GLN" => "Q", "GLU" => "E", "GLY" => "G", "HIS" => "H", "ILE" => "I",
        "LEU" => "L", "LYS" => "K", "MET" => "M", "PHE" => "F", "PRO" => "P",
        "SER" => "S", "THR" => "T", "TRP" => "W", "TYR" => "Y", "VAL" => "V",
        "SEC" => "U", "PYL" => "O", "ASX" => "B", "GLX" => "Z", "MSE" => "M",
        "DA" => "A", "DC" => "C", "DG" => "G", "DT" => "T",
        "A" => "A", "C" => "C", "G" => "G", "U" => "U",
    ];

    /**
     * @var string
     */
    private string $idCode = "";

    /**
     * @var string
     */
    private string $classification = "";

    /**
     * @var string
     */
    private string $depositionDate = "";

    /**
     * @var string
     */
    private string $title = "";

    /**
     * One block per molecule, keyed by token : MOL_ID, MOLECULE, CHAIN...
     * @var array
     */
    private array $compounds = [];

    /**
     * One block per molecule, keyed by token : MOL_ID, ORGANISM_SCIENTIFIC, STRAIN...
     * @var array
     */
    private array $sources = [];

    /**
     * @var array
     */
    private array $keywords = [];

    /**
     * @var string
     */
    private string $experimentalTechnique = "";

    /**
     * @var array
     */
    private array $authors = [];

    /**
     * @var array
     */
    private array $seqRes = [];

    /**
     * @var array
     */
    private array $helices = [];

    /**
     * @var array
     */
    private array $sheets = [];

    /**
     * @var array
     */
    private array $cryst1 = [];

    /**
     * @var array
     */
    private array $atoms = [];

    /**
     * @var array
     */
    private array $hetAtoms = [];

    /**
     * @var string
     */
    private string $sCompnd = "";

    /**
     * @var string
     */
    private string $sSource = "";

    /**
     * @var string
     */
    private string $sKeywds = "";

    /**
     * @var string
     */
    private string $sAuthor = "";

    /**
     * @var array
     */
    private array $aSeqResCodes = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "PDB";
    }

    /**
     * Tells whether a line opens a new PDB entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 6) == "HEADER";
    }

    /**
     * Tells whether a line closes a PDB entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return rtrim($sLine) == "END";
    }

    /**
     * Extracts the identifier uniquely naming a PDB entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        return trim(substr($sLine, 62, 4));
    }

    /**
     * Parses a PDB data file and populates this manager's fields and model objects.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile(array $aFlines) {
        // An NMR structure holds several models of the same atoms, each between MODEL n and ENDMDL.
        $iModel = 1;
        foreach ($aFlines as $sLine) {
            $sRecord = trim(substr($sLine, 0, 6));
            switch ($sRecord) {
                case "MODEL":
                    $iModel = (int) trim(substr($sLine, 10, 4));
                    break;
                case "HEADER":
                    $this->parseHeader($sLine);
                    break;
                case "TITLE":
                    $this->title = trim($this->title . " " . trim(substr($sLine, 10)));
                    break;
                case "COMPND":
                    $this->sCompnd .= " " . trim(substr($sLine, 10));
                    break;
                case "SOURCE":
                    $this->sSource .= " " . trim(substr($sLine, 10));
                    break;
                case "KEYWDS":
                    $this->sKeywds .= " " . trim(substr($sLine, 10));
                    break;
                case "EXPDTA":
                    $this->experimentalTechnique = trim($this->experimentalTechnique . " " . trim(substr($sLine, 10)));
                    break;
                case "AUTHOR":
                    $this->sAuthor .= " " . trim(substr($sLine, 10));
                    break;
                case "SEQRES":
                    $this->parseSeqRes($sLine);
                    break;
                case "HELIX":
                    $this->helices[] = $this->parseHelix($sLine);
                    break;
                case "SHEET":
                    $this->sheets[] = $this->parseSheet($sLine);
                    break;
                case "CRYST1":
                    $this->parseCryst1($sLine);
                    break;
                case "ATOM":
                    $this->atoms[] = $this->parseAtom($sLine, $iModel);
                    break;
                case "HETATM":
                    $this->hetAtoms[] = $this->parseAtom($sLine, $iModel);
                    break;
            }
        }

        $this->compounds = $this->parseSpecificationList($this->sCompnd);
        $this->sources = $this->parseSpecificationList($this->sSource);
        $this->keywords = array_values(array_filter(array_map('trim', explode(",", $this->sKeywds))));
        $this->authors = array_values(array_filter(array_map('trim', explode(",", $this->sAuthor))));

        foreach ($this->aSeqResCodes as $sChainId => $aCodes) {
            $sSequence = "";
            foreach ($aCodes as $sCode) {
                $sSequence .= self::$aminoAcidCodes[$sCode] ?? "X";
            }
            $this->seqRes[$sChainId] = $sSequence;
        }
    }

    /**
     * Parses a COMPND or SOURCE record, both written in what the PDB format calls a
     * specification list : "TOKEN: value;" pairs where each MOL_ID opens the block of one
     * molecule. Keeping the blocks apart is what ties a chain to the molecule it belongs to,
     * so a structure holding several molecules yields several blocks. Records of files older
     * than the specification are free text carrying no token, and stay plain strings.
     * @param   string      $sText
     * @return  array
     */
    private function parseSpecificationList(string $sText) : array {
        $aBlocks  = [];
        $aCurrent = [];

        foreach (array_filter(array_map('trim', explode(";", $sText))) as $sItem) {
            $aTokval = explode(":", $sItem, 2);

            if (count($aTokval) < 2) {
                if ($aCurrent != []) {
                    $aBlocks[] = $aCurrent;
                    $aCurrent  = [];
                }
                $aBlocks[] = $sItem;
                continue;
            }

            $sToken = trim($aTokval[0]);
            if ($sToken == "MOL_ID" && $aCurrent != []) {
                $aBlocks[] = $aCurrent;
                $aCurrent  = [];
            }
            $aCurrent[$sToken] = trim($aTokval[1]);
        }

        if ($aCurrent != []) {
            $aBlocks[] = $aCurrent;
        }

        return $aBlocks;
    }

    /**
     * Parses the HEADER line.
     * Columns : 11-50 classification, 51-59 deposition date, 63-66 idCode.
     * @param   string      $sLine
     */
    private function parseHeader(string $sLine) {
        $this->classification = trim(substr($sLine, 10, 40));
        $this->depositionDate = trim(substr($sLine, 50, 9));
        $this->idCode = trim(substr($sLine, 62, 4));
    }

    /**
     * Parses one SEQRES line and accumulates residue codes per chain.
     * Columns : 12 chainID, 20- residues (3-letter codes, space-separated).
     * @param   string      $sLine
     */
    private function parseSeqRes(string $sLine) {
        $sChainId = trim(substr($sLine, 11, 1));
        $aCodes = array_values(array_filter(preg_split("/\s+/", trim(substr($sLine, 19)))));

        if (!isset($this->aSeqResCodes[$sChainId])) {
            $this->aSeqResCodes[$sChainId] = [];
        }
        $this->aSeqResCodes[$sChainId] = array_merge($this->aSeqResCodes[$sChainId], $aCodes);
    }

    /**
     * Parses one HELIX line.
     * Columns : 12-14 helixID, 16-18 initResName, 20 initChainID, 22-25 initSeqNum, 26 initICode,
     * 28-30 endResName, 32 endChainID, 34-37 endSeqNum, 38 endICode, 39-40 helixClass, 72-76 length.
     * @param   string      $sLine
     * @return  PdbHelixInterface
     */
    private function parseHelix(string $sLine) : PdbHelixInterface {
        $oHelix = new PdbHelix();
        $oHelix->setHelixId(trim(substr($sLine, 11, 3)));
        $oHelix->setInitResName(trim(substr($sLine, 15, 3)));
        $oHelix->setInitChainId(trim(substr($sLine, 19, 1)));
        $oHelix->setInitSeqNum((int) trim(substr($sLine, 21, 4)));
        $oHelix->setInitICode(trim(substr($sLine, 25, 1)));
        $oHelix->setEndResName(trim(substr($sLine, 27, 3)));
        $oHelix->setEndChainId(trim(substr($sLine, 31, 1)));
        $oHelix->setEndSeqNum((int) trim(substr($sLine, 33, 4)));
        $oHelix->setEndICode(trim(substr($sLine, 37, 1)));
        $oHelix->setHelixClass((int) trim(substr($sLine, 38, 2)));
        $oHelix->setLength((int) trim(substr($sLine, 71, 5)));
        return $oHelix;
    }

    /**
     * Parses one SHEET line.
     * Columns : 8-10 strand, 12-14 sheetID, 18-20 initResName, 22 initChainID,
     * 23-26 initSeqNum, 27 initICode, 29-31 endResName, 33 endChainID, 34-37 endSeqNum, 38 endICode.
     * @param   string      $sLine
     * @return  PdbSheetInterface
     */
    private function parseSheet(string $sLine) : PdbSheetInterface {
        $oSheet = new PdbSheet();
        $oSheet->setStrand((int) trim(substr($sLine, 7, 3)));
        $oSheet->setSheetId(trim(substr($sLine, 11, 3)));
        $oSheet->setInitResName(trim(substr($sLine, 17, 3)));
        $oSheet->setInitChainId(trim(substr($sLine, 21, 1)));
        $oSheet->setInitSeqNum((int) trim(substr($sLine, 22, 4)));
        $oSheet->setInitICode(trim(substr($sLine, 26, 1)));
        $oSheet->setEndResName(trim(substr($sLine, 28, 3)));
        $oSheet->setEndChainId(trim(substr($sLine, 32, 1)));
        $oSheet->setEndSeqNum((int) trim(substr($sLine, 33, 4)));
        $oSheet->setEndICode(trim(substr($sLine, 37, 1)));
        return $oSheet;
    }

    /**
     * Parses the CRYST1 line.
     * Columns : 7-15 a, 16-24 b, 25-33 c, 34-40 alpha, 41-47 beta, 48-54 gamma,
     * 56-66 space group, 67-70 Z.
     * @param   string      $sLine
     */
    private function parseCryst1(string $sLine) {
        $this->cryst1 = [
            "a"          => (float) trim(substr($sLine, 6, 9)),
            "b"          => (float) trim(substr($sLine, 15, 9)),
            "c"          => (float) trim(substr($sLine, 24, 9)),
            "alpha"      => (float) trim(substr($sLine, 33, 7)),
            "beta"       => (float) trim(substr($sLine, 40, 7)),
            "gamma"      => (float) trim(substr($sLine, 47, 7)),
            "spaceGroup" => trim(substr($sLine, 55, 11)),
            "z"          => (int) trim(substr($sLine, 66, 4)),
        ];
    }

    /**
     * Parses one ATOM or HETATM line.
     * Columns : 7-11 serial, 13-16 name, 17 altLoc, 18-20 resName, 22 chainID,
     * 23-26 resSeq, 27 iCode, 31-38 x, 39-46 y, 47-54 z, 55-60 occupancy, 61-66 tempFactor,
     * 77-78 element.
     * @param   string      $sLine
     * @param   int         $iModel     The MODEL the line belongs to
     * @return  PdbAtomInterface
     */
    private function parseAtom(string $sLine, int $iModel) : PdbAtomInterface {
        $oAtom = new PdbAtom();
        $oAtom->setSerial((int) trim(substr($sLine, 6, 5)));
        $oAtom->setName(trim(substr($sLine, 12, 4)));
        $oAtom->setAltLoc(trim(substr($sLine, 16, 1)));
        $oAtom->setResName(trim(substr($sLine, 17, 3)));
        $oAtom->setChainId(trim(substr($sLine, 21, 1)));
        $oAtom->setResSeq((int) trim(substr($sLine, 22, 4)));
        $oAtom->setICode(trim(substr($sLine, 26, 1)));
        $oAtom->setModel($iModel);
        $oAtom->setX((float) trim(substr($sLine, 30, 8)));
        $oAtom->setY((float) trim(substr($sLine, 38, 8)));
        $oAtom->setZ((float) trim(substr($sLine, 46, 8)));
        $oAtom->setOccupancy((float) trim(substr($sLine, 54, 6)));
        $oAtom->setTempFactor((float) trim(substr($sLine, 60, 6)));
        $oAtom->setElement(trim(substr($sLine, 76, 2)));
        return $oAtom;
    }

    /**
     * @return string
     */
    public function getIdCode(): string
    {
        return $this->idCode;
    }

    /**
     * @return string
     */
    public function getClassification(): string
    {
        return $this->classification;
    }

    /**
     * @return string
     */
    public function getDepositionDate(): string
    {
        return $this->depositionDate;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getCompounds(): array
    {
        return $this->compounds;
    }

    /**
     * @return array
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @return array
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * @return string
     */
    public function getExperimentalTechnique(): string
    {
        return $this->experimentalTechnique;
    }

    /**
     * @return array
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    /**
     * @return array
     */
    public function getSeqRes(): array
    {
        return $this->seqRes;
    }

    /**
     * @return PdbHelixInterface[]
     */
    public function getHelices(): array
    {
        return $this->helices;
    }

    /**
     * @return PdbSheetInterface[]
     */
    public function getSheets(): array
    {
        return $this->sheets;
    }

    /**
     * @return array
     */
    public function getCryst1(): array
    {
        return $this->cryst1;
    }

    /**
     * @return PdbAtomInterface[]
     */
    public function getAtoms(): array
    {
        return $this->atoms;
    }

    /**
     * @return PdbAtomInterface[]
     */
    public function getHetAtoms(): array
    {
        return $this->hetAtoms;
    }
}
