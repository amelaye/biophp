<?php
/**
 * Parses a Newick string into a PhylogeneticNode tree
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Phylogenetics\Service;

use Amelaye\BioPHP\Domain\Phylogenetics\Exception\InvalidNewickException;
use Amelaye\BioPHP\Domain\Phylogenetics\Interfaces\NewickReaderInterface;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\PhylogeneticNode;

/**
 * A small recursive-descent parser over the classic Newick grammar :
 *   tree     := subtree ";"
 *   subtree  := ("(" subtree ("," subtree)* ")")? name? (":" number)?
 * Quoted labels ("'a name with spaces'") and NHX-style bracketed comments/annotations are not
 * supported - neither is used anywhere else in this project, and both would add a second, rarely
 * exercised code path for no current benefit. An unquoted label simply runs until the next
 * structural character ("(", ")", ",", ":", ";").
 * Class NewickReader
 * @package Amelaye\BioPHP\Domain\Phylogenetics\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class NewickReader implements NewickReaderInterface
{
    private const STRUCTURAL_CHARACTERS = "(),:;";

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

        if ($iPosition < strlen($sBody) && $sBody[$iPosition] === "(") {
            $iPosition++;
            $aChildren[] = $this->parseSubtree($sBody, $iPosition);

            while ($iPosition < strlen($sBody) && $sBody[$iPosition] === ",") {
                $iPosition++;
                $aChildren[] = $this->parseSubtree($sBody, $iPosition);
            }

            if ($iPosition >= strlen($sBody) || $sBody[$iPosition] !== ")") {
                throw InvalidNewickException::unbalancedParentheses($iPosition);
            }
            $iPosition++;
        }

        $sName = $this->parseToken($sBody, $iPosition);
        $fBranchLength = $this->parseBranchLength($sBody, $iPosition);

        return new PhylogeneticNode($sName === "" ? null : $sName, $fBranchLength, $aChildren);
    }

    /**
     * Reads characters up to the next structural character ; used for a node's name.
     * @param   string      $sBody
     * @param   int         $iPosition
     * @return  string
     */
    private function parseToken(string $sBody, int &$iPosition): string
    {
        $iStart = $iPosition;
        $iLength = strlen($sBody);

        while ($iPosition < $iLength && strpos(self::STRUCTURAL_CHARACTERS, $sBody[$iPosition]) === false) {
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
        $iStart = $iPosition;
        $sValue = $this->parseToken($sBody, $iPosition);

        if (!is_numeric($sValue)) {
            throw InvalidNewickException::invalidBranchLength($sValue, $iStart);
        }

        return (float) $sValue;
    }
}
