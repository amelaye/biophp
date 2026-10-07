<?php
/**
 * Parses a Newick string into a PhylogeneticNode tree
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Phylogenetics\Service;

use Amelaye\BioPHP\Domain\Phylogenetics\Exception\InvalidNewickException;
use Amelaye\BioPHP\Domain\Phylogenetics\Interfaces\NewickReaderInterface;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\PhylogeneticNode;

/**
 * A small recursive-descent parser over the Newick grammar (J. Felsenstein's specification) :
 *   tree     := subtree ";"
 *   subtree  := ("(" subtree ("," subtree)* ")")? label? (":" number)?
 * Blanks, tabs and newlines may appear anywhere but inside an unquoted label or a branch length,
 * and so may comments in square brackets, NHX annotations ("[&&NHX:S=human]") included : both are
 * skipped. A quoted label ('Homo sapiens (human)') may hold any character, a single quote being
 * written twice. An unquoted label runs until the next structural character, blank or comment.
 * Underscores are kept as they are : the specification reads them as blanks, but Biopython and ape
 * do not, and identifiers such as "seq_1" must still match the sequences they name.
 * Class NewickReader
 * @package Amelaye\BioPHP\Domain\Phylogenetics\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class NewickReader implements NewickReaderInterface
{
    private const STRUCTURAL_CHARACTERS = "(),:;[";

    /**
     * @param   string      $sNewick
     * @return  PhylogeneticNode
     * @throws  InvalidNewickException
     */
    public function read(string $sNewick): PhylogeneticNode
    {
        $sTrimmed = trim($sNewick);

        if ($sTrimmed === "") {
            throw InvalidNewickException::emptyString();
        }

        if (substr($sTrimmed, -1) !== ";") {
            throw InvalidNewickException::missingTerminator();
        }

        $sBody = substr($sTrimmed, 0, -1);
        $iPosition = 0;
        $oRoot = $this->parseSubtree($sBody, $iPosition);
        $this->skipBlanksAndComments($sBody, $iPosition);

        if ($iPosition !== strlen($sBody)) {
            throw InvalidNewickException::trailingContent($iPosition);
        }

        return $oRoot;
    }

    /**
     * @param   string      $sBody
     * @param   int         $iPosition  Advanced past whatever was consumed
     * @return  PhylogeneticNode
     */
    private function parseSubtree(string $sBody, int &$iPosition): PhylogeneticNode
    {
        $aChildren = [];
        $this->skipBlanksAndComments($sBody, $iPosition);

        if ($iPosition < strlen($sBody) && $sBody[$iPosition] === "(") {
            $iPosition++;
            $aChildren[] = $this->parseSubtree($sBody, $iPosition);
            $this->skipBlanksAndComments($sBody, $iPosition);

            while ($iPosition < strlen($sBody) && $sBody[$iPosition] === ",") {
                $iPosition++;
                $aChildren[] = $this->parseSubtree($sBody, $iPosition);
                $this->skipBlanksAndComments($sBody, $iPosition);
            }

            if ($iPosition >= strlen($sBody) || $sBody[$iPosition] !== ")") {
                throw InvalidNewickException::unbalancedParentheses($iPosition);
            }
            $iPosition++;
            $this->skipBlanksAndComments($sBody, $iPosition);
        }

        $sName = $this->parseLabel($sBody, $iPosition);
        $this->skipBlanksAndComments($sBody, $iPosition);
        $fBranchLength = $this->parseBranchLength($sBody, $iPosition);
        $this->skipBlanksAndComments($sBody, $iPosition);

        return new PhylogeneticNode($sName === "" ? null : $sName, $fBranchLength, $aChildren);
    }

    /**
     * Steps over blanks, tabs, newlines and [comments].
     * @param   string      $sBody
     * @param   int         $iPosition
     * @throws  InvalidNewickException  When a comment is never closed
     */
    private function skipBlanksAndComments(string $sBody, int &$iPosition): void
    {
        $iLength = strlen($sBody);

        while ($iPosition < $iLength) {
            if (ctype_space($sBody[$iPosition])) {
                $iPosition++;
            } elseif ($sBody[$iPosition] === "[") {
                $iEnd = strpos($sBody, "]", $iPosition);
                if ($iEnd === false) {
                    throw InvalidNewickException::unterminatedComment($iPosition);
                }
                $iPosition = $iEnd + 1;
            } else {
                break;
            }
        }
    }

    /**
     * Reads a node's label : quoted, with '' standing for a single quote, or unquoted.
     * @param   string      $sBody
     * @param   int         $iPosition
     * @return  string
     * @throws  InvalidNewickException  When a quoted label is never closed
     */
    private function parseLabel(string $sBody, int &$iPosition): string
    {
        if ($iPosition >= strlen($sBody) || $sBody[$iPosition] !== "'") {
            return $this->parseToken($sBody, $iPosition);
        }

        $iStart = $iPosition;
        $iLength = strlen($sBody);
        $sLabel = "";
        $iPosition++;
        while (true) {
            if ($iPosition >= $iLength) {
                throw InvalidNewickException::unterminatedQuotedLabel($iStart);
            }
            if ($sBody[$iPosition] === "'") {
                if (($sBody[$iPosition + 1] ?? "") !== "'") {
                    $iPosition++;
                    return $sLabel;
                }
                $iPosition++;
            }
            $sLabel .= $sBody[$iPosition];
            $iPosition++;
        }
    }

    /**
     * Reads characters up to the next structural character, blank or comment ; used for an unquoted
     * name and for a branch length.
     * @param   string      $sBody
     * @param   int         $iPosition
     * @return  string
     */
    private function parseToken(string $sBody, int &$iPosition): string
    {
        $iStart = $iPosition;
        $iLength = strlen($sBody);

        while ($iPosition < $iLength
            && strpos(self::STRUCTURAL_CHARACTERS, $sBody[$iPosition]) === false
            && !ctype_space($sBody[$iPosition])) {
            $iPosition++;
        }

        return substr($sBody, $iStart, $iPosition - $iStart);
    }

    /**
     * @param   string      $sBody
     * @param   int         $iPosition
     * @return  float|null  Null when no ":" is present at the current position
     */
    private function parseBranchLength(string $sBody, int &$iPosition): ?float
    {
        if ($iPosition >= strlen($sBody) || $sBody[$iPosition] !== ":") {
            return null;
        }

        $iPosition++;
        $this->skipBlanksAndComments($sBody, $iPosition);
        $iStart = $iPosition;
        $sValue = $this->parseToken($sBody, $iPosition);

        if (!is_numeric($sValue)) {
            throw InvalidNewickException::invalidBranchLength($sValue, $iStart);
        }

        return (float) $sValue;
    }
}
