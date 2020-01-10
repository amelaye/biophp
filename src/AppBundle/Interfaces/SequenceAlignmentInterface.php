<?php
/**
 * Sequence Alignment Managing
 * Freely inspired by BioPHP's project biophp.org
 * Created 10 january 2020
 * Last modified 10 january 2020
 */
namespace AppBundle\Interfaces;

use AppBundle\Entity\Sequencing\Sequence;

/**
 * Represents the result of an alignment performed by various third-party
 * software such as ClustalW. The alignment is usually found in a file that uses
 * a particular format. Right now, my code supports only FASTA and CLUSTAL formats.
 * Properties and methods allow users to perform post-alignment operations, manipulations, etc.
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @todo : length doit représenter la totalité des séquences
 */
interface SequenceAlignmentInterface
{
    /**
     * The sequences array ... then you can rewind(), next(), prev() on it
     * @return \ArrayIterator
     */
    public function getSeqSet() : \ArrayIterator;

    /**
     * Sets a specific filename : the file to parse
     * @param   string  $sFilename
     */
    public function setFilename($sFilename);

    /**
     * Sets a specific format : FASTA or CLUSTAL
     * @param   string  $sFormat
     */
    public function setFormat($sFormat);

    /**
     * Parses Clustal Files and create Sequence object
     * @throws  \Exception
     */
    public function parseClustal();

    /**
     * Parses Fasta Files and create Sequence object
     * @throws  \Exception
     * @Todo : setting start and stop
     */
    public function parseFasta();

    /**
     * Main method - Parses FASTA or CLUSTAL file
     * This "fetches" all sequences into the $aSeqSet property of the service.
     */
    public function parseFile();

    /**
     * Rearranges the sequences in an alignment set alphabetically by their sequence id.
     * In addition, you can specify if you wish it be in ascending or descending order via $sOption.
     * The sOption accepts either "ASC" or "DESC" in whatever case (uppercase, lowercase,
     * mixed case). This determines the sort order of the alignment set.
     * @param   string          $sOption    ASCendant or DESCendant
     * @throws  \Exception
     */
    public function sortAlpha(string $sOption = "ASC");

    /**
     * Returns the length of the longest sequence in an alignment set.
     * @return  int
     * @throws  \Exception
     */
    public function getMaxiLength() : int;

    /**
     * Counts the number of gaps ("-") found in all sequences in an alignment set.
     * @return  int         The number of "gap characters" in the all sequences in the alignment set.
     * @throws  \Exception
     */
    public function getGapCount() : int;

    /**
     * Tests if all the sequences in an alignment set have the same length.
     * @return  boolean
     * @throws  \Exception
     */
    public function getIsFlush() : bool;

    /**
     * Returns the character found at a given residue number in a given sequence.
     * @param   int         $iSeqIdx    Index of the sequence in the array
     * @param   int         $iRes       The residue number of the character we wish to get or extract
     * @return  boolean | string        A single character representing an amino acid residue or a "gap".
     * @throws  \Exception
     */
    public function charAtRes(int $iSeqIdx, int $iRes);

    /**
     * Gets the substring between two residues in a sequence that is part of an alignment set.
     * @param   int            $iSeqIdx     Index of the sequence in the array
     * @param   int            $iResStart   Start of the subsequence
     * @param   int            $iResEnd     End of the subsequence
     * @return  string | boolean            A substring within the specified sequence.
     * @throws  \Exception
     */
    public function substrBwRes(int $iSeqIdx, int $iResStart, int $iResEnd = 0);

    /**
     * Converts a column number to a residue number in a sequence that is part of an alignment set.
     * @param   int     $iSeqIdx    Index number of the desired sequence within the alignment set.
     * @param   int     $iCol       The column number which we want to convert to a residue number.
     * @return  boolean|string      An integer representing the residue number corresponding to the given column number.
     * @throws  \Exception
     */
    public function colToRes(int $iSeqIdx, int $iCol);

    /**
     * Converts a residue number to a column number in a sequence in an alignment set.
     * @param   int     $iSeqIdx    The index number of the desired sequence in the alignment set.
     * @param   int     $iRes       The residue number we wish to convert into a column number.
     * @return  boolean|int         An integer representing the column number corresponding to the given residue number.
     * @throws  \Exception
     */
    public function resToCol(int $iSeqIdx, int $iRes);

    /**
     * Creates a new alignment set from a series of contiguous/consecutive sequences.
     * @param   int     $iStart     The index number of the first sequence to include in the new SeqAlign object.
     * @param   int     $iEnd       The index number of the last sequence to include in the new SeqAlign object.
     * @throws  \Exception
     */
    public function subalign(int $iStart, int $iEnd);

    /**
     * Creates a new alignment set from non-consecutive sequences found in another existing alignment set.
     * @throws \Exception
     * @Todo : length is wrong
     */
    public function select();

    /**
     * Determines the index position of both variant and invariant residues according
     * to a given "percentage threshold" similar to that in the consensus() method.
     * @param   int         $iThreshold    a number between 0 to 100, indicating the percentage threshold below
     * which the current index position is considered variant, and on or above which the current
     * index position is considered invariant. If omitted, this is set to 100 by default.
     * @return  array
     * @throws  \Exception
     */
    public function resVar(int $iThreshold = 100) : array;

    /**
     * Returns the consensus string for an alignment set.  See technical reference for details.
     * @param   int         $iThreshold     A number between 0 to 100, indicating the percentage threshold before
     * (or below which) the unknown character "?" is used in a particular position or column in the
     * consensus string. If omitted, this is set to 100 by default.
     * @return  string                      The consensus string formed according to the given threshold.
     * @throws  \Exception
     */
    public function consensus(int $iThreshold = 100) : string;

    /**
     * Adds a sequence to an alignment set. It does not perform any sequence alignment.
     * @param   Sequence     $oSequence     The object to be added to the alignment set.
     * @return  int                         The number of sequences in the alignment set after the call.
     * @throws  \Exception
     */
    public function addSequence(Sequence $oSequence) : int;

    /**
     * Deletes or removes a sequence from an alignment set.
     * @param   string      $iSequenceId    The id of the sequence to be deleted from the alignment set.
     * @return  int                         The number of sequences in the alignment set after the call.
     * @throws  \Exception
     */
    public function deleteSequence(string $iSequenceId) : int;
}