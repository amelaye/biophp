<?php
/**
 * Restriction enzyme Interface
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 10 january 2020
 * Last modified 18 january 2020
 */
namespace App\Domain\Sequence\Interfaces;

use App\Domain\Sequence\Entity\Enzyme;
use App\Domain\Sequence\Service\SequenceManager;

/**
 * Interface RestrictionEnzymeInterface - substances that can "cut" a DNA strand
 * into two or more fragments along special sites called restriction sites. They
 * are an important tool in recombinant DNA technology.
 * @package App\Domain\Sequence\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface RestrictionEnzymeInterface
{
    /**
     * Sets a new enzyme element
     */
    public function setEnzyme();

    /**
     * @return Enzyme
     */
    public function getEnzyme() : Enzyme;

    /**
     * Sets a sequence object
     * @param SequenceManager $sequenceManager
     */
    public function setSequenceManager(SequenceManager $sequenceManager);

    /**
     * It creates a new Enzyme object and initializes its properties accordingly.
     * If passed with make = 'custom', object will be added to aRestEnzimDB.
     * If not, the function will attemp to retrieve data from aRestEnzimDB.
     * If unsuccessful in retrieving data, it will return an error flag.
     * @param   string      $sName
     * @param   string      $sPattern
     * @param   string      $sCutpos
     * @param   string      $sMake
     * @throws  \Exception
     * @todo : penser à faire une factory
     */
    public function parseEnzyme(string $sName, string $sPattern, string $sCutpos, string $sMake = "custom");

    /**
     * Cuts a DNA sequence into fragments using the restriction enzyme object.
     * @param   string             $options            May be "N" or "O".  If "N", the sequence is cut using the patpos() group
     * of methods (no overlapping patterns).  If "O", the sequence is cut using the patposo() group
     * of methods (with overlapping patterns). If omitted, this defaults to "N".
     * @return  array       An array of fragments (substrings of the parameter sequence)
     * @throws  \Exception
     */
    public function cutSeq(string $options = "N") : array;

    /**
     * Returns the pattern associated with a given restriction endonuclease.
     * @param   string      $RestEn_Name
     * @return  string      The sequence pattern (string) recognized by the given restriction enzyme.
     */
    public function getPattern(string $RestEn_Name) : string;

    /**
     * Returns the cutting position of the restriction enzyme object.
     * @param   string      $RestEn_Name
     * @return  int         Returns the cutting position (an integer) of the restriction enzyme object.
     */
    public function getCutPos(string $RestEn_Name) : int;

    /**
     * Returns the length of the cutting pattern of the restriction enzyme object.
     * @param   string  $RestEn_Name
     * @return  int     The length (integer) of the restriction pattern recognized by the enzyme.
     */
    public function getLength(string $RestEn_Name = "") : int;

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
    public function findRestEn(string $sPattern = null, int $iCutpos = null, int $iPlen = null) : array;
}